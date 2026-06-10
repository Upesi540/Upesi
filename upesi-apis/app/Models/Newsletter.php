<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email',
        'name',
        'user_id',
        'preferences',
        'subscribed_ip',
        'subscribed_at',
        'unsubscribed_at',
        'is_active'
    ];

    protected $casts = [
        'preferences' => 'array',
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Relation avec l'utilisateur (si inscrit via compte)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour les abonnés actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->whereNull('unsubscribed_at');
    }

    /**
     * Désabonner
     */
    public function unsubscribe()
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now()
        ]);
    }

    /**
     * Réabonner
     */
    public function resubscribe()
    {
        $this->update([
            'is_active' => true,
            'unsubscribed_at' => null
        ]);
    }
}
