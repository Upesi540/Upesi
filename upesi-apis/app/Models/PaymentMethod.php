<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'code',
        'name',
        'category',
        'description',
        'logo_url',
        'configuration',
        'countries',
        'operators',
        'sort_order',
        'is_active',
        'is_instant',
        'requires_phone',
        'requires_account',
        'validation_rules'
    ];

    protected $casts = [
        'configuration' => 'array',
        'countries' => 'array',
        'operators' => 'array',
        'validation_rules' => 'array',
        'is_active' => 'boolean',
        'is_instant' => 'boolean',
        'requires_phone' => 'boolean',
        'requires_account' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Relations
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_method_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeAvailableInCountry($query, $countryCode)
    {
        return $query->whereJsonContains('countries', $countryCode);
    }

    public function scopeInstant($query)
    {
        return $query->where('is_instant', true);
    }

    /**
     * Vérifie si disponible dans un pays
     */
    public function isAvailableInCountry($countryCode): bool
    {
        if (!$this->countries) {
            return true; // Pas de restriction
        }

        return in_array($countryCode, $this->countries);
    }

    /**
     * Calcule les frais pour un montant
     */
    public function calculateFees(float $amount): float
    {
        if (!$this->configuration || !isset($this->configuration['fees'])) {
            return 0;
        }

        $fees = $this->configuration['fees'];

        if (isset($fees['percentage'])) {
            return $amount * ($fees['percentage'] / 100);
        }

        if (isset($fees['fixed'])) {
            return $fees['fixed'];
        }

        return 0;
    }

    /**
     * Vérifie si le montant est dans les limites
     */
    public function isAmountWithinLimits(float $amount): bool
    {
        if (!$this->configuration) {
            return true;
        }

        $limits = $this->configuration['limits'] ?? null;

        if ($limits) {
            if (isset($limits['min']) && $amount < $limits['min']) {
                return false;
            }
            if (isset($limits['max']) && $amount > $limits['max']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne le libellé complet pour l'affichage
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->operators && count($this->operators) > 0) {
            return $this->name . ' (' . implode(', ', $this->operators) . ')';
        }

        return $this->name;
    }

    /**
     * Catégories disponibles
     */
    public static function getCategories(): array
    {
        return [
            'mobile_money' => 'Mobile Money',
            'card' => 'Cartes bancaires',
            'bank' => 'Virements bancaires',
            'crypto' => 'Cryptomonnaies',
            'cash' => 'Espèces',
            'wallet' => 'Portefeuilles électroniques',
            'other' => 'Autres'
        ];
    }
}
