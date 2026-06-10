<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'crop_id',
        'country_id',
        'state_id',
        'city_id',
        'min_price',
        'max_price',
        'average_price',
        'volume_quantity',
        'unit_id',
        'source_count',
        'recorded_at',
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'average_price' => 'decimal:2',
        'volume_quantity' => 'decimal:2',
        'source_count' => 'integer',
        'recorded_at' => 'date',
    ];

    // Dans App\Models\PriceHistory.php
    protected static function booted()
    {
        static::saved(function ($priceHistory) {
            // Option radicale : on vide tout le cache lié aux tendances
            // Ou plus précis si tu veux gérer les clés une par une
            \Illuminate\Support\Facades\Cache::flush();
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
    /**
     * Relation avec le produit de référence
     */
    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    /**
     * Relation avec l'unité de mesure
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Le nom de la fonction doit commencer par "scope"
     * Mais quand tu l'appelles, tu ignores le mot "scope"
     */
    public function scopeForCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    // Filtrer par région
    public function scopeForState($query, $stateId)
    {
        return $query->where('state_id', $stateId);
    }

    // Filtrer par ville
    public function scopeForCity($query, $cityId)
    {
        return $query->where('city_id', $cityId);
    }
}
