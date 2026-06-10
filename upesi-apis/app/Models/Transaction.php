<?php

namespace App\Models;

use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'transactions';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        // Identifiants
        'tid',
        'reference',
        'gateway',

        // Montants
        'amount',
        'fees',
        'amount_transferred',
        'amount_debited',
        'commission',
        'fee_breakdown',

        // Type, statut, opération
        'type',
        'status',
        'operation',
        'mode',

        // Description et metadata
        'description',
        'token',
        'metadata',
        'provider_response',

        // Relations
        'user_id',
        'wallet_id',
        'currency_id',
        'wallet_transaction_id',

        // Dates
        'expire_at',
        'declined_at',
        'canceled_at',
        'transferred_at',
        'refunded_at',
        'processed_at',

        // Gestion erreurs
        'retry_count',
        'error_message',
        'last_sync_at',
    ];

    protected $casts = [
        // Montants - 8 décimales pour Bitcoin
        'amount' => 'decimal:8',
        'fees' => 'decimal:8',
        'amount_transferred' => 'decimal:8',
        'amount_debited' => 'decimal:8',
        'commission' => 'decimal:8',

        // JSON
        'fee_breakdown' => 'array',
        'metadata' => 'array',
        'provider_response' => 'array',

        // Dates
        'expire_at' => 'datetime',
        'declined_at' => 'datetime',
        'canceled_at' => 'datetime',
        'transferred_at' => 'datetime',
        'refunded_at' => 'datetime',
        'processed_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',

        // Autres
        'retry_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending',
        'retry_count' => 0,
    ];

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function walletTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_transaction_id');
    }

    /**
     * Scopes utiles
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'pending_retry']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByGateway($query, string $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDeposits($query)
    {
        return $query->where('operation', 'deposit');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('operation', 'withdrawal');
    }

    /**
     * Méthodes utilitaires
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['completed', 'approved', 'transferred']);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'pending_retry']);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'cancelled', 'declined']);
    }

    public function markAsCompleted(?string $walletTransactionId = null): void
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'wallet_transaction_id' => $walletTransactionId ?? $this->wallet_transaction_id,
        ]);
    }

    public function markAsFailed(?string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage ?? $this->error_message,
        ]);
    }

    public function incrementRetry(): void
    {
        $this->increment('retry_count');
        $this->update(['last_sync_at' => now()]);
    }

    /**
     * Accesseurs
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 0) . ' ' . ($this->currency?->code ?? 'FCFA');
    }

    public function getGatewayLabelAttribute(): string
    {
        return match ($this->gateway) {
            'fedapay' => 'FedaPay',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            default => ucfirst($this->gateway ?? 'Inconnu')
        };
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = ReferenceGenerator::generate('TX');
            }
        });
    }
}
