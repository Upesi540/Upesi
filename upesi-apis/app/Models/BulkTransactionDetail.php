<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkTransactionDetail extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'bulk_transaction_id',
        'merchant_profile_id',
        'participant_type',
        'product_name',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'commission_rate',
        'commission_amount',
        'participant_gets',
        'product_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'participant_gets' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Participant types
    const PARTICIPANT_SELLER = 'seller';
    const PARTICIPANT_BUYER = 'buyer';

    const PARTICIPANT_TYPES = [
        self::PARTICIPANT_SELLER => 'Vendeur',
        self::PARTICIPANT_BUYER => 'Acheteur',
    ];

    /**
     * Relation avec la transaction groupée parente
     */
    public function bulkTransaction(): BelongsTo
    {
        return $this->belongsTo(BulkTransaction::class);
    }

    /**
     * Relation avec le profil marchand (producteur, fournisseur, etc.)
     */
    public function merchantProfile(): BelongsTo
    {
        return $this->belongsTo(MerchantProfile::class);
    }

    /**
     * Relation avec le produit (optionnel)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Récupérer l'utilisateur associé au merchant profile
     */
    public function getUserAttribute()
    {
        return $this->merchantProfile?->user;
    }

    /**
     * Accesseur: label du type de participant
     */
    public function getParticipantTypeLabelAttribute(): string
    {
        return self::PARTICIPANT_TYPES[$this->participant_type] ?? $this->participant_type;
    }

    /**
     * Accesseur: nom complet du participant
     */
    public function getParticipantNameAttribute(): string
    {
        return $this->merchantProfile?->user?->full_name ?? 'Inconnu';
    }

    /**
     * Accesseur: montant formaté
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Mutateur: recalcule automatiquement subtotal, commission et net
     */
    public function recalculate(): void
    {
        $this->subtotal = $this->quantity * $this->unit_price;
        $this->commission_amount = ($this->subtotal * $this->commission_rate) / 100;
        $this->participant_gets = $this->subtotal - $this->commission_amount;
    }

    /**
     * Scope: vendeurs
     */
    public function scopeSellers($query)
    {
        return $query->where('participant_type', self::PARTICIPANT_SELLER);
    }

    /**
     * Scope: acheteurs
     */
    public function scopeBuyers($query)
    {
        return $query->where('participant_type', self::PARTICIPANT_BUYER);
    }

    /**
     * Scope: par type de profil marchand
     */
    public function scopeWhereMerchantType($query, string $type)
    {
        return $query->whereHas('merchantProfile', function($q) use ($type) {
            $q->where('type', $type);
        });
    }
}
