<?php
// app/Http/Resources/ServiceOfferResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $categorySlug = $this->service->category->slug ?? 'prestation'; // 'prestation' par défaut

        // On prépare les images
        $images = collect($this->images ?? [])->map(function ($image) {
            if (str_starts_with($image, 'http')) {
                return $image;
            }
            return Storage::url($image);
        })->values()->all();

        // LOGIQUE FALLBACK : Si pas d'images, on met le logo par défaut de la catégorie
        if (empty($images)) {
            // On vérifie si c'est de la logistique ou autre
            $defaultLogo = ($categorySlug === 'logistique')
                ? 'services/logos/logistique-logo.png'
                : 'services/logos/prestation-logo.png';

            $images = [Storage::url($defaultLogo)];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'service_name' => $this->service->name ?? null,
            'service_category' => $categorySlug,
            'merchant' => [
                'id' => $this->merchantProfile->id,
                'shop_name' => $this->merchantProfile->shop_name,
                'type' => $this->merchantProfile->type,
                'type_label' => $this->merchantProfile->type_label,
                'user' => [
                    'id' => $this->merchantProfile->user->id,
                    'name' => trim(($this->merchantProfile->user->first_name ?? '') . ' ' . ($this->merchantProfile->user->last_name ?? '')),
                ],
            ],
            'images' => $images,

            // Après
            'service_zones' => $this->zones_names, // Tableau de noms
            // Gardez aussi éventuellement les IDs si besoin côté front
            'service_zones_ids' => $this->service_zones,
            'created_at' => $this->created_at,
        ];
    }
}
