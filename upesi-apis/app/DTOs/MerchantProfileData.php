<?php
namespace App\DTOs;

class MerchantProfileData
{
    public function __construct(
        public string $shop_name,
        public string $type,
        public ?string $phone,
        public ?string $description,
        public array $metadata,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            shop_name: $data['shop_name'],
            type: $data['type'],
            phone: $data['phone'] ?? null,
            description: $data['description'] ?? null,
            metadata: self::buildMetadata($data['type'], $data),
        );
    }

    private static function buildMetadata(string $type, array $data): array
    {
        return match($type) {
            'producer' => [
                'address' => $data['address'] ?? null,
                'location' => $data['location'] ?? null,
                'surface' => $data['surface'] ?? null,
                'crops' => $data['crops'] ?? [],
            ],
            'trader' => [
                'address' => $data['address'] ?? null,
                'location' => $data['location'] ?? null,
                'crops' => $data['crops'] ?? [],
            ],
            'transporter' => [
                'address' => $data['address'] ?? null,
                'location' => $data['location'] ?? null,
                'transport_type' => $data['transport_type'] ?? [],
                'vehicle_type' => $data['vehicle_type'] ?? [],
            ],
            'provider' => [
                'personal_address' => $data['personal_address'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'service_zone' => $data['service_zone'] ?? [],
                'service_type' => $data['service_type'] ?? [],
                'other_service' => $data['other_service'] ?? null,
            ],
            'supplier' => [
                'personal_address' => $data['personal_address'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'location' => $data['location'] ?? null,
                'categories' => $data['categories'] ?? [],
            ],
            default => [],
        };
    }
}
