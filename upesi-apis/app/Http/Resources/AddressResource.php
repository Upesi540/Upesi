<?php
// app/Http/Resources/AddressResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'is_default' => $this->is_default,

            // Adresse complète formatée
            'full_address' => $this->getFullAddressAttribute(),

            // Champs individuels
            'address_line' => $this->address_line,
            'city' => $this->city,
            'prefecture' => $this->prefecture,
            'postal_code' => $this->postal_code,

            // Coordonnées géographiques
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            // Relations avec chargement conditionnel
            'user' => new UserResource($this->whenLoaded('user')),
            'country' => new CountryResource($this->whenLoaded('country')),
            // 'state' => new StateResource($this->whenLoaded('state')),

            // Pour les maps (optionnel)
            'maps' => [
                'google_maps' => $this->latitude && $this->longitude
                    ? "https://www.google.com/maps?q={$this->latitude},{$this->longitude}"
                    : null,
                'open_street_map' => $this->latitude && $this->longitude
                    ? "https://www.openstreetmap.org/?mlat={$this->latitude}&mlon={$this->longitude}"
                    : null,
            ],

            // Métadonnées
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Accesseur pour l'adresse complète formatée
     */
    protected function getFullAddressAttribute(): string
    {
        $parts = [];

        if ($this->address_line) {
            $parts[] = $this->address_line;
        }

        if ($this->city) {
            $parts[] = $this->city;
        }

        if ($this->state && $this->state->name) {
            $parts[] = $this->state->name;
        }

        if ($this->country && $this->country->name) {
            $parts[] = $this->country->name;
        }

        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }

        return implode(', ', $parts);
    }
}
