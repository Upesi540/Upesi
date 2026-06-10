<?php
// app/Http/Resources/WalletResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'available_balance' => $this->available_balance,
            'frozen_balance' => $this->frozen_balance,
            'total_balance' => $this->available_balance + $this->frozen_balance,
            'formatted_available' => number_format($this->available_balance, 0, ',', ' ') . ' ' . ($this->currency->symbol ?? 'FCFA'),
            'is_active' => $this->is_active,
            'is_primary' => $this->when($this->user_id, function() {
                return $this->user->primary_wallet_id === $this->id;
            }),
            'currency' => new CurrencyResource($this->whenLoaded('currency')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
