<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'subscription_id',
        'template_id',
        'batch_id',
        'status',
        'error_message',
        'sent_at',
        'clicked_at',
        'provider_response'
    ];

    protected $casts = [
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(NotificationTemplate::class);
    }
}
