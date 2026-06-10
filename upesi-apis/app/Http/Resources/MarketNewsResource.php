<?php
// app/Http/Resources/MarketNewsResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MarketNewsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image ? Storage::url($this->featured_image) : null,
            'type' => $this->type,
            'priority' => $this->priority,
            'is_pinned' => $this->is_pinned,
            'is_active' => $this->is_active,
            'reading_time' => $this->reading_time,
            'formatted_date' => $this->formatted_date,
            'published_at' => $this->published_at?->toIso8601String(),
            'tags' => $this->tags ?? [],

            'author' => $this->whenLoaded('author', fn() => [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->author->profile_photo_url,
            ]),

            'category' => $this->whenLoaded('newsCategory', fn() => [
                'id' => $this->newsCategory->id,
                'name' => $this->newsCategory->name,
                'slug' => $this->newsCategory->slug,
            ]),

            'meta' => $this->when($request->routeIs('news.show'), fn() => [
                'title' => $this->meta_tags['title'] ?? $this->title,
                'description' => $this->meta_tags['description'] ?? $this->excerpt,
                'keywords' => $this->meta_tags['keywords'] ?? '',
                'published_time' => $this->published_at?->toIso8601String(),
            ]),
        ];
    }
}
