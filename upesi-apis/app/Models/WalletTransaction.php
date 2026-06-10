<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'wallet_transactions';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'wallet_id',
        'currency_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'operation_type',
        'transactionable_type',
        'transactionable_id',
        'description',
        'metadata',
        'status',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'decimal:4',
        'balance_before' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    /**
     * Wallet lié à la transaction
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Devise de la transaction
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Relation polymorphique
     * (order, trade, payout, withdrawal, etc.)
     */
    public function transactionable(): MorphTo
    {
        return $this->morphTo();
    }
}
