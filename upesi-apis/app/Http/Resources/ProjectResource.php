<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'description' => $this->description, // déjà casté en array
            'image_path'  => $this->image_path ? Storage::url($this->image_path ) : null,
            'gallery'     => $this->gallery ? array_map(fn($img) => Storage::url($img), $this->gallery) : [],
            'client'      => $this->client,
            'start_date'  => $this->start_date?->toISOString(),
            'end_date'    => $this->end_date?->toISOString(),
            'status'      => $this->status,
            'location'    => $this->location,
            'testimonials'=> $this->testimonials,
            'sort_order'  => $this->sort_order,
            'is_active'   => $this->is_active,
            'duration'    => $this->duration,       // via accessor
            'is_ongoing'  => $this->is_ongoing,     // via accessor
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
