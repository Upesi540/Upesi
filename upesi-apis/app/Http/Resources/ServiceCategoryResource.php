<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon? Storage::url($this->icon):null,

            // On affiche les services seulement s'ils sont chargés (Eager Loading)
            'services' => ServiceResource::collection($this->whenLoaded('services')),

            'sort_order' => $this->sort_order,
        ];
    }
}
