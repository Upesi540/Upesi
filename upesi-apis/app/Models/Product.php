<?php

namespace App\Models;

use App\Helpers\ReferenceGenerator;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'merchant_profile_id', // Nouveau : Relation boutique
        'crop_id',
        'currency_id',         // Nouveau : Relation devise
        'unit_id',
        'title',
        'description',
        'sku',
        'images',
        'quantity',
        'min_order_quantity',
        'unit_price',
        'city_id',
        'state_id',
        'country_id',
        'warehouse_name',
        'address',
        'latitude',
        'longitude',
        'status',
        'harvest_info',
        'is_featured'
    ];

    protected $casts = [
        'images' => 'array',
        'harvest_info' => 'array',
        'is_featured' => 'boolean',
        'quantity' => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Le propriétaire commercial du produit
     */
    public function merchantProfile(): BelongsTo
    {
        return $this->belongsTo(MerchantProfile::class, 'merchant_profile_id');
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    /**
     * Devise utilisée pour le prix unitaire
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // --- Boot Logic ---

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            // 1. Gestion du SKU (Automatique)
            if (empty($product->sku)) {
                $product->sku = ReferenceGenerator::generate('PRD', 8);
            }
            $currencyCode = config('app.base_currency');
            $currency = Currency::where('code', $currencyCode)->first();

            $product->currency_id=$currency->id;
        });

        // Nettoyage automatique du cache lors d'un changement
        $clearCache = fn() => Cache::forget('homepage_data');

        static::created($clearCache);
        static::updated($clearCache);
        static::deleted($clearCache);
    }

    // --- Accessors ---

    /**
     * Retourne le nom complet avec le type de culture
     */
    public function getFullNameAttribute(): string
    {
        $cropName = $this->crop?->name ?? 'Produit';
        return $cropName . ($this->title ? " - " . $this->title : "");
    }
}
