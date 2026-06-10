<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkTransaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'trader_id',
        'type',
        'status',
        'total_amount',
        'trader_commission',
        'counterparty_id',
        'validated_by',
        'validated_at',
        'validation_notes',
        'metadata',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'trader_commission' => 'decimal:2',
        'validated_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Types constants
    const TYPE_SALE = 'sale';
    const TYPE_PURCHASE = 'purchase';

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_COMPLETED = 'completed';

    // Type labels
    const TYPES = [
        self::TYPE_SALE => 'Vente groupée',
        self::TYPE_PURCHASE => 'Achat groupé',
    ];

    // Status labels
    const STATUSES = [
        self::STATUS_DRAFT => 'Brouillon',
        self::STATUS_PENDING => 'En attente validation',
        self::STATUS_APPROVED => 'Approuvé',
        self::STATUS_REJECTED => 'Rejeté',
        self::STATUS_COMPLETED => 'Complété',
    ];

    // Status colors
    const STATUS_COLORS = [
        self::STATUS_DRAFT => 'gray',
        self::STATUS_PENDING => 'warning',
        self::STATUS_APPROVED => 'success',
        self::STATUS_REJECTED => 'danger',
        self::STATUS_COMPLETED => 'info',
    ];

    /**
     * Relation avec le trader (négociant)
     */
    public function trader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trader_id');
    }

    /**
     * Récupérer le profil merchant du trader (type 'trader')
     */
    public function traderMerchantProfile()
    {
        return $this->hasOne(MerchantProfile::class, 'user_id', 'trader_id')
            ->where('type', 'trader');
    }

    /**
     * Relation avec la contrepartie (client ou fournisseur)
     */
    public function counterparty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counterparty_id');
    }

    /**
     * Relation avec l'admin qui a validé
     */
    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec les détails de la transaction
     */
    public function details(): HasMany
    {
        return $this->hasMany(BulkTransactionDetail::class);
    }

    /**
     * Relation avec les commandes liées
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'bulk_transaction_orders')
            ->withTimestamps();
    }

    /**
     * Vérifier si la transaction peut être soumise
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->details()->count() > 0;
    }

    /**
     * Vérifier si la transaction peut être validée
     */
    public function canBeValidated(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Vérifier si la transaction peut être complétée
     */
    public function canBeCompleted(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Soumettre pour validation
     */
    public function submitForValidation(): void
    {
        if (!$this->canBeSubmitted()) {
            throw new \Exception('La transaction ne peut pas être soumise');
        }

        $this->update(['status' => self::STATUS_PENDING]);
    }

    /**
     * Approuver la transaction
     */
    public function approve(?string $notes = null): void
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('La transaction ne peut pas être approuvée');
        }

        DB::transaction(function () use ($notes) {
            // 1. Récupérer le wallet du client (pour vente) ou des acheteurs (pour achat)
            if ($this->type === 'sale') {
                // VENTE : le client paie → argent séquestré
                $buyerWallet = Wallet::where('user_id', $this->counterparty_id)->first();

                if (!$buyerWallet) {
                    throw new \Exception('Wallet du client non trouvé');
                }

                // Séquestrer le montant total
                $this->walletService->holdFunds(
                    wallet: $buyerWallet,
                    amount: $this->total_amount,
                    reference: $this,
                    sellerId: $this->trader_id,
                    description: "Séquestre vente groupée #{$this->reference}"
                );
            } else {
                // ACHAT : les acheteurs paient individuellement
                foreach ($this->details as $detail) {
                    $buyerWallet = Wallet::where('user_id', $detail->merchantProfile->user_id)->first();

                    if ($buyerWallet) {
                        $this->walletService->holdFunds(
                            wallet: $buyerWallet,
                            amount: $detail->participant_gets,
                            reference: $this,
                            sellerId: $this->counterparty_id,
                            description: "Séquestre achat groupé #{$this->reference} - {$detail->product_name}"
                        );
                    }
                }
            }

            // 2. Marquer comme approuvé
            $this->update([
                'status' => self::STATUS_APPROVED,
                'validated_by' => Auth::id(),
                'validated_at' => now(),
                'validation_notes' => $notes,
            ]);
        });
    }

    /**
     * Rejeter la transaction
     */
    public function reject(?string $reason = null): void
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('La transaction ne peut pas être rejetée');
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'validation_notes' => $reason,
        ]);
    }

    /**
     * Marquer comme complété
     */
    /**
     * Marquer comme complété et gérer le stock
     */
    public function complete(): void
    {
        if (!$this->canBeCompleted()) {
            throw new \Exception('La transaction ne peut pas être complétée');
        }

        DB::transaction(function () {
            if ($this->type === 'sale') {
                // VENTE : libérer l'argent aux participants
                $hold = $this->walletService->findPendingHold($this, $this->trader_id);

                if ($hold) {
                    // Libérer les fonds (débiter client, créditer participants)
                    $this->walletService->releaseFunds($hold, 0);
                }

                // Créditer chaque participant
                foreach ($this->details as $detail) {
                    $participantWallet = Wallet::where('user_id', $detail->merchantProfile->user_id)->first();

                    if ($participantWallet) {
                        $participantWallet->modifyBalance(
                            amount: $detail->participant_gets,
                            type: 'credit',
                            opType: 'sale',
                            desc: "Vente groupée #{$this->reference}",
                            source: $this
                        );
                    }
                }
            } else {
                // ACHAT : libérer l'argent au fournisseur
                $supplierWallet = Wallet::where('user_id', $this->counterparty_id)->first();

                if ($supplierWallet) {
                    $supplierWallet->modifyBalance(
                        amount: $this->total_amount,
                        type: 'credit',
                        opType: 'purchase',
                        desc: "Achat groupé #{$this->reference}",
                        source: $this
                    );
                }
            }

            // Créditer le négociant
            $traderWallet = Wallet::where('user_id', $this->trader_id)->first();
            if ($traderWallet && $this->trader_commission > 0) {
                $traderWallet->modifyBalance(
                    amount: $this->trader_commission,
                    type: 'credit',
                    opType: 'commission',
                    desc: "Commission #{$this->reference}",
                    source: $this
                );
            }

            $this->status = self::STATUS_COMPLETED;
            $this->save();
        });
    }

    /**
     * Accesseur: label du type
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Accesseur: label du statut
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Accesseur: couleur du statut
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    /**
     * Accesseur: référence unique
     */
    public function getReferenceAttribute(): string
    {
        return 'BT-' . strtoupper(substr($this->id, 0, 8));
    }

    /**
     * Scope: transactions en attente de validation
     */
    public function scopePendingValidation($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: transactions approuvées
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: par type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: par trader
     */
    public function scopeForTrader($query, string $traderId)
    {
        return $query->where('trader_id', $traderId);
    }
}
