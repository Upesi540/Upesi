<?php
// app/Http/Resources/UnitResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'description' => $this->description,
            'is_active' => $this->is_active,

            // Exemples formatés pour l'affichage
            'examples' => [
                'display' => "1 {$this->symbol} ({$this->name})",
                'price_format' => "1000 {$this->symbol}/kg",
            ],

            // Relations conditionnelles
            'crops' => CropResource::collection($this->whenLoaded('crops')),
            'products' => ProductResource::collection($this->whenLoaded('products')),
            // 'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),

            // Statistiques conditionnelles
            'crops_count' => $this->when($this->crops_count !== null, $this->crops_count),
            'products_count' => $this->when($this->products_count !== null, $this->products_count),
            'order_items_count' => $this->when($this->order_items_count !== null, $this->order_items_count),
        ];
    }
}
