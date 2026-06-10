<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import crucial
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Slide extends Model
{
    use HasFactory, HasUuids; // On ajoute le trait HasUuids

    protected $fillable = [
        'title',
        'sub_title',
        'button_text',
        'link_type',
        'link_url',
        'button_color',
        'button_text_color',
        'image_path',
        'order',
        'is_active',
    ];
    // On ajoute les casts ici
    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    protected static function booted()
    {
        static::saved(fn() => Cache::forget('home_slides'));
        static::deleted(fn() => Cache::forget('home_slides'));
        
    }
}
