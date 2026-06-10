<?php

namespace Database\Seeders;

use App\Models\Market;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            [
                'name' => 'Intrants Agricole',
                'description' => 'Semences, engrais, pesticides et autres intrants agricoles.',
                'image_path' => 'markets/logos/intrants-logo.png',
                'banner_path' => 'markets/banners/intrants-banner.jpg',
            ],
            [
                'name' => 'Matières Premières Agricole',
                'description' => 'Produits bruts issus de l’agriculture.',
                'image_path' => 'markets/logos/matieres-logo.png',
                'banner_path' => 'markets/banners/matieres-banner.jpg',
            ],
            [
                'name' => 'Agro-Alimentaire',
                'description' => 'Produits transformés issus de l’agriculture.',
                'image_path' => 'markets/logos/agro-logo.png',
                'banner_path' => 'markets/banners/agro-banner.jpg',
            ],
        ];

        foreach ($markets as $marketData) {

            Market::firstOrCreate(
                ['slug' => Str::slug($marketData['name'])],
                [
                    'name' => $marketData['name'],
                    'slug' => Str::slug($marketData['name']),
                    'description' => $marketData['description'],

                    'image_path' => $marketData['image_path'],
                    'banner_path' => $marketData['banner_path'],

                    'meta_title' => $marketData['name'],
                    'meta_description' => $marketData['description'],

                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }
    }
}
