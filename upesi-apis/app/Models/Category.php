<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'market_id',
        'icon',
        'image_path',
        'sort_order',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // --- RELATIONSHIPS ---

    /**
     * Récupère la catégorie parente
     */
    // public function parent(): BelongsTo
    // {
    //     return $this->belongsTo(Category::class, 'parent_id');
    // }

    /**
     * Récupère les sous-catégories directes
     */
    // public function children(): HasMany
    // {
    //     return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    // }

        // --- RELATIONSHIPS ---

    /**
     * Récupère la catégorie parente
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class, 'market_id');
    }

    /**
     * Une catégorie a plusieurs crops
     */
    public function crops(): HasMany
    {
        return $this->hasMany(Crop::class);
    }

    // --- OPTIMISATION DES COMPTEURS (SANS SATURER LE SERVEUR) ---

    /**
     * Pour Filament et l'API : Utilisation de withCount()
     * Cette méthode permet de récupérer le nombre de relations via une seule requête SQL
     * au lieu de charger tous les modèles en mémoire.
     */

    // public function scopeWithCounts(Builder $query): void
    // {
    //     $query->withCount(['children', 'products']);
    // }
}
