<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'merchant_profile_id',
        'product_name',
        'quantity',
        'unit_id',
        'unit_price',
        'subtotal',
        'tax',
        'discount',
        'total',
        // Champs multi-vendeurs
        'seller_status',
        'seller_confirmed_at',
        'seller_shipped_at',
        'seller_delivered_at',
        'seller_paid_at',
        'tracking_number',
        'shipping_carrier',
        'commission_rate',
        'commission_amount',
        'seller_gets',
        // 👇 AJOUTER CES DEUX LIGNES
        'cancelled_by',
        'cancellation_reason',
        // Anciens champs
        'metadata',
        'is_custom_item',
        'custom_description'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_custom_item' => 'boolean',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'seller_gets' => 'decimal:2',
        'seller_confirmed_at' => 'datetime',
        'seller_shipped_at' => 'datetime',
        'seller_delivered_at' => 'datetime',
        'seller_paid_at' => 'datetime',
        // 👇 AJOUTER CES DEUX LIGNES (optionnel, si tu veux garder la date)
        'cancelled_at' => 'datetime',
    ];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function merchantProfile()
    {
        return $this->belongsTo(MerchantProfile::class, 'merchant_profile_id');
    }

    public function seller()
    {
        return $this->merchantProfile ? $this->merchantProfile->user : null;
    }

    // Accesseurs
    public function getSellerStatusLabelAttribute(): string
    {
        return match ($this->seller_status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En préparation',
            'shipped' => 'Expédié',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => ucfirst($this->seller_status),
        };
    }

    public function getSellerStatusColorAttribute(): string
    {
        return match ($this->seller_status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'secondary',
            default => 'gray',
        };
    }

    public function canSellerCancel(): bool
    {
        return in_array($this->seller_status, ['pending', 'confirmed']);
    }

    public function canBuyerCancel(): bool
    {
        return in_array($this->seller_status, ['pending', 'confirmed']);
    }

    // Scopes
    public function scopeForMerchant($query, $merchantProfileId)
    {
        return $query->where('merchant_profile_id', $merchantProfileId);
    }

    public function scopePendingForSeller($query)
    {
        return $query->where('seller_status', 'pending');
    }

    public function scopeNotDelivered($query)
    {
        return $query->whereNotIn('seller_status', ['delivered', 'cancelled', 'refunded']);
    }
}
