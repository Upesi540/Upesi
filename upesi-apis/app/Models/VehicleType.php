<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class VehicleType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function merchantProfiles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MerchantProfile::class, 'vehicle_type_id');
    }
}
