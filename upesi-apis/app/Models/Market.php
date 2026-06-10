<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Market extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Les attributs pouvant être assignés massivement.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'banner_path',
        'meta_title',
        'meta_description',
        'is_active',
        'sort_order',
    ];

    /**
     * Les types de conversion des colonnes.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * --- RELATIONS ---
     */

    /**
     * Un marché propose plusieurs services.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Un marché a plusieurs crops (via les catégories)
     */
    public function crops(): HasManyThrough
    {
        return $this->hasManyThrough(Crop::class, Category::class);
    }

    /**
     * Un marché a plusieurs produits (via catégories → crops)
     */
    public function products(): HasManyThrough
    {
        return $this->through('categories')
            ->has('crops')
            ->has('products');
    }

    /**
     * --- SCOPES (Pratique pour Filament) ---
     */

    /**
     * Scope pour ne récupérer que les marchés actifs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted()
    {
        static::saved(fn() => Cache::forget('upesi_global_navigation'));
        static::deleted(fn() => Cache::forget('upesi_global_navigation'));
    }
}
