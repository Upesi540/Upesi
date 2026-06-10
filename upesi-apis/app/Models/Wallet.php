<?php

namespace App\Models;

use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Wallet extends Model
{
    use HasFactory, HasUuids;


    protected $fillable = [
        'user_id',
        'currency_id',
        'holder_type',
        'available_balance',
        'is_active',
    ];

    protected $casts = [
        'available_balance' => 'decimal:4',
        'is_active' => 'boolean',
    ];
    public static function booted()
    {
        // 🔒 GARDE-FOU : empêche la création d'un second wallet
        static::creating(function ($wallet) {
            if ($wallet->user_id) {
                $exists = self::where('user_id', $wallet->user_id)->exists();

                if ($exists) {
                    throw new \Exception("Un utilisateur ne peut avoir qu'un seul wallet.");
                } else {
                    $currencyCode = config('app.base_currency');
                    $currency = Currency::where('code', $currencyCode)->first();

                    $wallet->currency_id=$currency->id;
                }
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }
    /**
     * Formate le solde avec la devise
     */
    public function getFormattedAvailableBalanceAttribute(): string
    {
        return number_format($this->available_balance, 2) . ' ' . ($this->currency->symbol ?? 'FCFA');
    }
    /**
     * Modifie le solde de façon atomique.
     * Note le "?Model" pour éviter la dépréciation PHP 8.4
     */
    public function modifyBalance(float $amount, string $type, string $opType, ?string $desc = null, ?Model $source = null): WalletTransaction
    {
        return DB::transaction(function () use ($amount, $type, $opType, $desc, $source) {
            $before = $this->available_balance;

            if ($type === 'credit') {
                $this->available_balance += $amount;
            } else {
                if (!$this->hasSufficientBalance($amount)) throw new \Exception("Solde insuffisant");
                $this->available_balance -= $amount;
            }

            $this->save();

            return $this->transactions()->create([
                'currency_id' => $this->currency_id,
                'reference'   => ReferenceGenerator::generate($type === 'credit' ? 'CR' : 'DB'),
                'type' => $type,
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $this->available_balance,
                'operation_type' => $opType,
                'description' => $desc,
                'transactionable_type' => $source ? get_class($source) : null,
                'transactionable_id' => $source ? $source->id : null,
                'status' => 'completed',
            ]);
        });
    }

    public static function getSystemCommission(string $currencyId)
    {
        return self::where('holder_type', 'system_commission')->where('currency_id', $currencyId)->first();
    }

    public static function getSystemEscrow(string $currencyId)
    {
        return self::where('holder_type', 'system_escrow')->where('currency_id', $currencyId)->first();
    }
}
