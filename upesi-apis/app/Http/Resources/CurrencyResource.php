<?php
// app/Http/Resources/CurrencyResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'exchange_rate' => $this->exchange_rate,
            'is_base' => $this->is_base,
            'is_active' => $this->is_active,
            'precision' => $this->precision,
            'is_crypto' => $this->is_crypto,

            // Métadonnées pour l'affichage
            'formatted' => [
                'with_symbol' => '0.00 ' . $this->symbol,
                'with_code' => '0.00 ' . $this->code,
            ],

            // Relations conditionnelles
            'wallets' => WalletResource::collection($this->whenLoaded('wallets')),

            // Statistiques conditionnelles
            'wallets_count' => $this->when($this->wallets_count !== null, $this->wallets_count),

            // Méthodes utilitaires accessibles depuis l'API
            'exchange_rate_date' => $this->updated_at?->toIso8601String(),

        ];
    }
}
