<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    use HasUuids;

    protected $fillable = ['user_id', 'token', 'platform', 'device_name', 'is_active', 'last_used_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
