<?php
// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Récupération du vendeur via le profil marchand
        $merchant = $this->merchantProfile;
        $seller = $merchant?->user;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'sku' => $this->sku,
            'description' => $this->description,
            'images' => collect($this->images ?? [])->map(function ($image) {
                if (str_starts_with($image, 'http')) {
                    return $image;
                }
                return Storage::url($image);
            })->values()->toArray(),
            'quantity' => $this->quantity,
            'min_order_quantity' => $this->min_order_quantity,
            'unit_price' => $this->unit_price,
            'status' => $this->status,
            'harvest_info' => $this->harvest_info,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at,

            // Vendeur (pour compatibilité frontend)
            'user' => $seller ? [
                'id' => $seller->id,
                'name' => $seller->first_name . ' ' . $seller->last_name,
                'first_name' => $seller->first_name,
                'last_name' => $seller->last_name,
                'profile_photo_url' => $seller->profile_photo_path,
                'email' => $seller->email,
                'phone' => $seller->phone,
            ] : null,

            // Profil marchand (si nécessaire)
            'merchant_profile' => $merchant ? [
                'id' => $merchant->id,
                'shop_name' => $merchant->shop_name,
                'type' => $merchant->type,
                'type_label' => $merchant->type_label,
            ] : null,

            // Relations (chargement conditionnel)
            'crop' => new CropResource($this->whenLoaded('crop')),
            'unit' => new UnitResource($this->whenLoaded('unit')),

            // Localisation
            'location' => [
                'city' => $this->whenLoaded('city', fn() => $this->city->name),
                'state' => $this->whenLoaded('state', fn() => $this->state->name),
                'country' => $this->whenLoaded('country', fn() => $this->country->name),
                'address' => $this->address,
                'warehouse' => $this->warehouse_name,
                'coordinates' => [
                    'lat' => $this->latitude,
                    'lng' => $this->longitude,
                ]
            ],
        ];
    }
}
