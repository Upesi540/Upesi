<?php

namespace App\Models;

use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MarketNews extends Model
{
    use HasFactory, HasUuids, SoftDeletes, HasOwner;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'meta_data',
        'type',
        'priority',
        'author_id',
        'tags',
        'published_at',
        'expires_at',
        'is_pinned',
        'is_active'
    ];

    protected $casts = [
        'content' => 'array',
        'meta_data' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function getOwnerColumn(): string
    {
        return 'author_id'; // Nom par défaut
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            if (empty($journal->slug)) {
                $journal->slug = Str::slug($journal->title) . '-' . uniqid();
            }

            if (empty($journal->excerpt) && !empty($journal->content)) {
                $journal->excerpt = Str::limit(strip_tags($journal->content), 150);
            }

            if (empty($journal->published_at)) {
                $journal->published_at = now();
            }
        });
    }



    /**
     * Relation avec l'auteur
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relation avec les cultures concernées
     */
    public function newsCategory()
    {
        return $this->belongsTo(NewsCategory::class);
    }

    /**
     * Scope pour les flash infos (priorité haute)
     */
    public function scopeFlashInfos($query)
    {
        return $query->where('type', 'flash')
            ->where('priority', 'high')
            ->where('is_active', true)
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope pour les actualités récentes
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->where('is_active', true)
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->orderBy('is_pinned', 'desc')
            ->orderBy('published_at', 'desc')
            ->limit($limit);
    }

    /**
     * Scope pour les articles non expirés
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Vérifie si l'article est expiré
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Récupère le temps de lecture estimé
     */
    public function getReadingTimeAttribute()
    {
        // Si content est un tableau, le convertir en JSON puis en texte
        $contentString = is_array($this->content) ? json_encode($this->content) : $this->content;
        $text = strip_tags($contentString);
        $words = str_word_count($text);
        $minutes = ceil($words / 200);
        return $minutes . ' min de lecture';
    }

    /**
     * Génère les meta tags pour le SEO
     */
    public function getMetaTagsAttribute()
    {
        $defaultMeta = [
            'title' => $this->meta_data['title'] ?? $this->title,
            'description' => $this->meta_data['description'] ?? $this->excerpt,
            'keywords' => $this->meta_data['keywords'] ?? implode(', ', $this->tags ?? []),
            'author' => $this->author->name ?? 'Bourse Agricole',
            'published_time' => $this->published_at->toIso8601String(),
        ];

        return array_merge($defaultMeta, $this->meta_data ?? []);
    }

    /**
     * Formate la date pour l'affichage
     */
    public function getFormattedDateAttribute()
    {
        return $this->published_at->isoFormat('LLL');
    }

    /**
     * Vérifie si c'est une urgence
     */
    public function getIsUrgentAttribute()
    {
        return $this->priority === 'urgent';
    }
}
