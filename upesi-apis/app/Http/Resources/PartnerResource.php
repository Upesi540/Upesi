<?php
// app/Http/Resources/PartnerResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'level' => $this->level,
            'logo_url' => $this->logo_path ? Storage::url($this->logo_path) : null,
            'cover_url' => $this->cover_image ? Storage::url($this->cover_image) : null,
            'website_url' => $this->website_url,
            'facebook_url' => $this->facebook_url,
            'description' => $this->description,
            'short_description' => $this->short_description,
        ];
    }
}
