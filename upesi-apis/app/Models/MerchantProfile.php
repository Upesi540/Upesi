<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MerchantProfile extends Model
{
    use HasUuids, HasOwner, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shop_name',
        'logo_path',
        'phone',
        'description',
        'type',
        'status',
        'metadata',
        'approved_by'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    // Types disponibles
    public const TYPES = [
        'producer' => 'Producteur',
        'supplier' => 'Fournisseur',
        'trader' => 'Négociant',
        'provider' => 'Prestataire',
        'transporter' => 'Transporteur',
    ];
    // Types disponibles
    public const TYPESWITHOUTTRADER = [
        'producer' => 'Producteur',
        'supplier' => 'Fournisseur',
        'provider' => 'Prestataire',
        'transporter' => 'Transporteur',
    ];

    // Statuts disponibles
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';


    /**
     * Boot du modèle
     */
    protected static function booted()
    {
        static::created(function ($merchantProfile) {
            $user = $merchantProfile->user;
            // IGNORER super_admin ET admin
            if (
                $user &&
                !$user->hasRole('super_admin') &&
                !$user->hasRole('admin') &&
                !$user->hasRole('merchant')
            ) {
                $user->assignRole('merchant');
            }
        });
    }
    /**
     * Lien vers l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }



    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class, 'transporter_profile_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'provider_profile_id');
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED &&
            $this->kyc &&
            $this->kyc->status === 'approved';
    }

    /**
     * Lien vers l'admin qui a approuvé
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function merchantLegalAgreements(): HasMany
    {
        return $this->hasMany(MerchantLegalAgreement::class, 'merchant_profile_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(MerchantDocument::class);
    }

    // Scopes utiles
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Accesseurs
    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
        ][$this->status] ?? $this->status;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    // Accesseurs spécifiques selon le type
    public function getCropsAttribute(): array
    {
        if ($this->type === 'producer') {
            return $this->metadata['crops'] ?? [];
        }
        return [];
    }

    public function getVehicleAttribute(): ?array
    {
        if ($this->type === 'transporter') {
            return $this->metadata['vehicle'] ?? null;
        }
        return null;
    }


    // Pr ventes groupées

    /**
     * Détails des transactions groupées où ce profil participe
     */
    public function bulkTransactionDetails(): HasMany
    {
        return $this->hasMany(BulkTransactionDetail::class);
    }

    /**
     * Transactions groupées où ce profil est vendeur
     */
    public function bulkTransactionsAsSeller()
    {
        return $this->belongsToMany(BulkTransaction::class, 'bulk_transaction_details', 'merchant_profile_id', 'bulk_transaction_id')
            ->where('participant_type', 'seller')
            ->withPivot('quantity', 'unit_price', 'subtotal', 'participant_gets');
    }

    /**
     * Transactions groupées où ce profil est acheteur
     */
    public function bulkTransactionsAsBuyer()
    {
        return $this->belongsToMany(BulkTransaction::class, 'bulk_transaction_details', 'merchant_profile_id', 'bulk_transaction_id')
            ->where('participant_type', 'buyer')
            ->withPivot('quantity', 'unit_price', 'subtotal', 'participant_gets');
    }
}
