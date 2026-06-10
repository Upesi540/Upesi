<?php
// app/Http/Resources/CategoryResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'sort_order' => $this->sort_order,
            'market_id' => $this->market_id,
            'products_count' => $this->when($this->products_count !== null, $this->products_count),
            'children_count' => $this->when($this->children_count !== null, $this->children_count),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
