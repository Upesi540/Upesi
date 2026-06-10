<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'slug',
        'version',
        'content',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    // Logique automatique pour ne laisser qu'une seule version active par slug
    protected static function booted()
    {
        static::saving(function ($document) {
            if ($document->is_active) {
                static::where('slug', $document->slug)
                    ->where('id', '!=', $document->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
