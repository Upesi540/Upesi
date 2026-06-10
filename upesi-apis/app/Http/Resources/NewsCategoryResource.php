<?php
// app/Http/Resources/NewsCategoryResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,

            // Compteur d'articles actifs (optionnel)
            'articles_count' => $this->when($this->marketNews_count !== null,
                $this->marketNews_count
            ),

            // Articles récents de cette catégorie (si chargés)
            'recent_articles' => MarketNewsResource::collection(
                $this->whenLoaded('marketNews', fn() =>
                    $this->marketNews->take(3)
                )
            ),
        ];
    }
}
