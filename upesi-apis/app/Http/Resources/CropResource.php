<?php
// app/Http/Resources/CropResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CropResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'variety' => $this->variety,
            'grade' => $this->grade,
            'scientific_name' => $this->scientific_name,
            'reference_price' => $this->reference_price,
            'price_updated_at' => $this->price_updated_at,
            'description' => $this->description,
            'image_url' => $this->image ? Storage::url($this->image) : null,
            'category_id' => $this->category_id,
            'default_unit' => new UnitResource($this->whenLoaded('defaultUnit')),
            'quality_standards' => $this->quality_standards,
            'growing_seasons' => $this->growing_seasons,
            'growing_days' => $this->growing_days,
            'attributes' => $this->attributes,
            'is_active' => $this->is_active,
            'products_count' => $this->when($this->products_count !== null, $this->products_count),
        ];
    }
}
