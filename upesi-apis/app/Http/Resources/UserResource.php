<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name, // Le champ 'name' brut
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'full_name'         => $this->full_name, // Via l'accessor du modèle
            'email'             => $this->email,
            'phone'             => $this->phone,
            'prefecture'        => $this->prefecture,
            'profile_photo_url' => $this->profile_photo_url, // Utilise l'accessor getProfilePhotoUrlAttribute

            // Statuts regroupés pour la clarté
            'status' => [
                'is_active'         => $this->is_active,
                'is_approved'       => $this->is_approved,
                'is_banned'         => $this->is_banned,
                'email_verified_at' => $this->email_verified_at,
                'is_deleted'        => $this->trashed(), // Pour savoir s'il est en soft delete
            ],

            // Sécurité (Indicateurs seulement, jamais les secrets)
            'security' => [
                'has_2fa_enabled'   => $this->has_email_authentication,
                'has_verified_email' => $this->hasVerifiedEmail(),
            ],

            // Relations (chargées conditionnellement)
            'country'            => new CountryResource($this->whenLoaded('country')),
            'preferred_currency' => new CurrencyResource($this->whenLoaded('preferredCurrency')),
            'merchant_profiles'   => MerchantProfileResource::collection($this->whenLoaded('merchantProfiles')),

            // Portefeuille (Wallet unique selon ton modèle HasOne)
            'wallet' => new WalletResource($this->whenLoaded('wallet')),

            // Collections
            'roles'       => $this->whenLoaded('roles', fn() => $this->getRoleNames()),
            'permissions' => $this->whenLoaded('permissions', fn() => $this->getAllPermissions()->pluck('name')),
            'addresses'   => AddressResource::collection($this->whenLoaded('addresses')),

            // Statistiques (Via withCount dans le controller)
            // Dans UserResource.php
            'stats' => [
                'products_count'   => $this->products_count ?? 0,
                'orders_as_buyer_count'  => $this->orders_as_buyer_count ?? 0,
                'orders_as_seller_count' => $this->orders_as_seller_count ?? 0,
                'merchant_profiles_count'  => $this->merchant_profiles_count ?? 0,
                'service_requests_count' => $this->service_requests_as_provider_or_transporter_count ?? 0
            ],

            // Dates
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
