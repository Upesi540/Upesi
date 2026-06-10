<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_base',
        'is_active',
        'precision',
        'is_crypto',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'is_base' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Relation avec les wallets
     */
    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    /**
     * Scope pour la devise de base
     */
    public function scopeBase($query)
    {
        return $query->where('is_base', true);
    }

    /**
     * Scope pour les devises actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Récupère la devise de base
     */
    public static function getBase()
    {
        return self::where('is_base', true)->first();
    }

    /**
     * Convertit un montant depuis la devise de base
     */
    public function convertFromBase(float $amountInBase): float
    {
        return $amountInBase * $this->exchange_rate;
    }

    /**
     * Convertit un montant vers la devise de base
     */
    public function convertToBase(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Formate un montant dans cette devise
     */
    public function format(float $amount): string
    {
        return number_format($amount, 2) . ' ' . $this->symbol;
    }

    /**
     * Boot du modèle
     */
    protected static function boot()
    {
        parent::boot();

        // Empêcher d'avoir plusieurs devises de base
        static::saving(function ($currency) {
            if ($currency->is_base) {
                static::where('id', '!=', $currency->id)
                    ->where('is_base', true)
                    ->update(['is_base' => false]);
            }
        });

        // Empêcher de désactiver ou supprimer la devise de base
        static::deleting(function ($currency) {
            if ($currency->is_base) {
                throw new \Exception("Cannot delete base currency");
            }
        });

        static::updating(function ($currency) {
            if ($currency->is_base && !$currency->is_base) {
                throw new \Exception("Cannot unset base currency");
            }
        });
    }
}
