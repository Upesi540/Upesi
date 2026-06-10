<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NotificationTemplate extends Model
{
    use HasUuids;

    protected $fillable = [
        'slug', 'title', 'body', 'icon_url',
        'image_url', 'action_url', 'priority', 'payload'
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
