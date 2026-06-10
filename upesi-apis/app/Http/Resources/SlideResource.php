<?php
// app/Http/Resources/SlideResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SlideResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'button_text' => $this->button_text,
            'link_type' => $this->link_type,
            'link_url' => $this->link_url,
            'button_color' => $this->button_color,
            'button_text_color' => $this->button_text_color,
            'image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}
