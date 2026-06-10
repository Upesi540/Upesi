<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crop extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'variety',
        'grade',         // Ajouté : Niveau de qualité (Grade 1, A, etc.)
        'scientific_name',
        'description',
        'image',          // Ajouté : Image de référence admin
        'category_id',
        'default_unit_id',
        'quality_standards', // Ajouté : Standards techniques JSON
        'growing_seasons',
        'growing_days',
        'attributes',
        'is_active'
    ];

    protected $casts = [
        'grade' => 'array',
        'variety' => 'array',
        'growing_seasons'   => 'array',
        'attributes'        => 'array',
        'quality_standards' => 'array', // Casté en array pour manipulation facile
        'is_active'         => 'boolean',
        'growing_days'      => 'integer',
        'reference_price' => 'decimal:2',
        'price_updated_at' => 'datetime',
    ];

    /**
     * Le marché/secteur est accessible via la catégorie (transitivité)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function defaultUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'default_unit_id');
    }
    // Crop.php
    public function market()
    {
        return $this->hasOneThrough(Market::class, Category::class, 'id', 'id', 'category_id', 'market_id');
    }
    /**
     * Les offres réelles postées par les vendeurs/agriculteurs
     */
    public function products(): HasMany
    {
        // Si ta table s'appelle 'offers', change Product::class en Offer::class
        return $this->hasMany(Product::class);
    }


    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_crops')
            ->withPivot('relation_type', 'notes', 'is_active')
            ->withTimestamps();
    }
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function latestPrice()
    {
        return $this->hasOne(PriceHistory::class)->latestOfMany('recorded_at');
    }
}
