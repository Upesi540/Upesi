<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MarketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            // Gestion des visuels
            'image' => $this->image_path ? Storage::url($this->image_path) : null,
            'banner' => $this->banner_path ? Storage::url($this->banner_path) : null,

            // On inclut les catégories uniquement si elles sont chargées (Eager Loading)
            // C'est ce qui évite de charger trop de données sur des listes simples
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),

            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,

            // On ne met pas les metas ici pour garder la ressource légère pour la navigation
            // Si tu en as besoin pour une page SEO, tu peux créer une MarketShowResource
        ];
    }
}
