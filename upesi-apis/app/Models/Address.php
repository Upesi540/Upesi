<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'label',
        'contact_name',
        'contact_phone',
        'country_id',
        'state_id',
        'city_id', // Changé city (string) en city_id (FK) selon ta migration
        'prefecture',
        'address_line',
        'postal_code',
        'latitude',
        'longitude',
        'type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Logique de démarrage du modèle
     */
    protected static function booted(): void
    {
        // Avant de créer ou de mettre à jour une adresse
        static::saving(function (Address $address) {
            // Si cette adresse est définie comme "par défaut"
            if ($address->is_default) {
                // On passe toutes les autres adresses de cet utilisateur à false
                static::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id) // Sauf celle qu'on manipule
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    // --- Relations ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
