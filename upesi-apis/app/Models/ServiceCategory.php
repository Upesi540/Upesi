<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ServiceCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'is_active',
        'sort_order',
    ];
    // Dans ServiceCategory.php
    protected static function booted()
    {
        static::saved(fn() => Cache::forget('upesi_global_navigation'));
        static::deleted(fn() => Cache::forget('upesi_global_navigation'));
    }
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
