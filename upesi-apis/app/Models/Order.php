<?php

namespace App\Models;

use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'order_number',
        'buyer_id',
        // ❌ SUPPRIMÉ: 'merchant_profile_id' (plus dans orders)
        'status',
        'payment_status',
        'payment_method_id',
        'payment_reference',
        'subtotal',
        'tax',
        'shipping_cost',
        'service_fee',
        'discount',
        'total',
        'currency_id',
        'shipping_address',
        'billing_address',
        'address_id',
        'notes',
        'metadata',
        'ordered_at',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'metadata' => 'array',
        'ordered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!$order->order_number) {
                $order->order_number = ReferenceGenerator::generate('ORD', 8);
            }
        });
    }

    // Relations
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    // ❌ SUPPRIMÉ: merchantProfile() (plus dans orders)

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    // Accesseurs
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'info',
            'delivered' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'secondary',
            default => 'gray',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'secondary',
            default => 'gray',
        };
    }

    public function getPaymentMethodNameAttribute(): ?string
    {
        return $this->paymentMethod?->name;
    }

    public function getPaymentMethodLogoAttribute(): ?string
    {
        return $this->paymentMethod?->logo_url;
    }

    public function isInstantPayment(): bool
    {
        return $this->paymentMethod?->is_instant ?? false;
    }

    // Scopes
    public function scopeForBuyer($query, $userId)
    {
        return $query->where('buyer_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'refunded']);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
