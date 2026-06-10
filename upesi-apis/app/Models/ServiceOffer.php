<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceOffer extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'merchant_profile_id',
        'service_id',
        'title',
        'description',
        'images',
        'price',
        'price_unit',
        'service_zones',
        'location_name',
        'latitude',
        'longitude',
        'is_available',
        'is_featured',
        'is_verified',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'images' => 'array',
        'service_zones' => 'array',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
    ];

    // Relations
    public function merchantProfile(): BelongsTo
    {
        return $this->belongsTo(MerchantProfile::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_available', true);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByCategory($query, $categorySlug)
    {
        return $query->whereHas('service.category', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    // Relation (optionnelle, mais utile)
    public function zones()
    {
        return $this->belongsToMany(State::class, 'service_offer_state', 'service_offer_id', 'state_id');
    }

    // Accesseur pour récupérer les noms des zones
    public function getZonesNamesAttribute()
    {
        if (empty($this->service_zones) || !is_array($this->service_zones)) {
            return [];
        }

        return State::whereIn('id', $this->service_zones)
            ->pluck('name')
            ->toArray();
    }
}
