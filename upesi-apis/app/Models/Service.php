<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'service_category_id', // nouveau champ
        'name',
        'slug',
        'description',
        'icon',
        'image_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta_keywords' => 'array', // Conversion automatique JSON <-> Array
    ];

    /**
     * La catégorie du service
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Les prestataires / utilisateurs proposant ce service
     */
    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_services', // pivot table
            'service_id',
            'merchant_profile_id'
        )->whereHas('services', function ($query) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', 'prestation');
            });
        })->withPivot('price', 'zone', 'is_active');
    }

    /**
     * Les transporteurs proposant ce service (optionnel, filtré par catégorie logistique)
     */
    public function transporters(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_services',
            'service_id',
            'merchant_profile_id'
        )->whereHas('services', function ($query) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', 'logistique');
            });
        })->withPivot('price', 'zone', 'is_active');
    }
}
