<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Delivery extends Model
{
    use HasUuids;

    const STATUS_PENDING = 'pending';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'order_item_id',
        'transporter_profile_id',
        'tracking_number',
        'status',
        'pickup_address',
        'delivery_address',
        'estimated_pickup_at',
        'estimated_delivery_at',
        'picked_up_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'estimated_pickup_at' => 'datetime',
        'estimated_delivery_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function transporter()
    {
        return $this->belongsTo(MerchantProfile::class, 'transporter_profile_id');
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }
}
