<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image_path',
        'gallery',
        'client',
        'start_date',
        'end_date',
        'status',
        'location',
        'testimonials',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'description' => 'array',
        'gallery' => 'array',
        'testimonials' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accesseurs
    public function getDurationAttribute()
    {
        if (!$this->start_date) return null;
        $end = $this->end_date ?? now();
        $months = $this->start_date->diffInMonths($end); // devrait être un entier, mais si décimal...
        $months = round($months); // arrondir
        return $months . ' ' . ($months > 1 ? 'mois' : 'mois');
    }

    public function getIsOngoingAttribute()
    {
        return $this->status === 'ongoing';
    }
}
