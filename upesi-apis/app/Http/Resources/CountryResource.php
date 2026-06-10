<?php
// app/Http/Resources/CountryResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'iso3' => $this->iso3,
            'iso2' => $this->iso2,
            'phone_code' => $this->phone_code,
            'capital' => $this->capital,
            'currency' => $this->currency,
            'native' => $this->native,
            'emoji' => $this->emoji,
            'emojiU' => $this->emojiU,

            // Relations conditionnelles
            // 'states' => StateResource::collection($this->whenLoaded('states')),

            // Statistiques conditionnelles
            'states_count' => $this->when($this->states_count !== null, $this->states_count),
            'users_count' => $this->when($this->users_count !== null, $this->users_count),
            'addresses_count' => $this->when($this->addresses_count !== null, $this->addresses_count),
        ];
    }
}
