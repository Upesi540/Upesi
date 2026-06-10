<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'cover_image',
        'website_url',
        'facebook_url',
        'type',
        'level',
        'description',
        'short_description',
        'internal_contact_name',
        'internal_contact_email',
        'sort_order',
        'is_active',
        'show_on_home',
    ];

    /**
     * Boot function pour générer le slug automatiquement si vide
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($partner) {
            if (empty($partner->slug)) {
                $partner->slug = Str::slug($partner->name);
            }
        });
    }

    /**
     * Casts pour les types de données
     */
    protected $casts = [
        'is_active' => 'boolean',
        'show_on_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope pour récupérer uniquement les partenaires actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les partenaires à afficher sur la home
     */
    public function scopeFeatured($query)
    {
        return $query->where('show_on_home', true)->orderBy('sort_order');
    }
}
