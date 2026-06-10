<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Prestation',
                'description' => 'Services agricoles comme labour, semis, récolte, traitement phytosanitaire, etc.',
                'icon' => "services/logos/prestation-logo.png",        // à remplir plus tard (ex: 'tractor.svg')
                'banner_path' => null, // à remplir plus tard si besoin
                'sort_order' => 1,
            ],
            [
                'name' => 'Logistique',
                'description' => 'Transport de produits agricoles, livraison, stockage et distribution.',
                'icon' => "services/logos/logistique-logo.png",        // à remplir plus tard (ex: 'truck.svg')
                'banner_path' => null, // à remplir plus tard si besoin
                'sort_order' => 2,
            ],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::updateOrCreate(
                ['slug' => Str::slug($cat['name'])], // clé unique
                [
                    'name' => $cat['name'],
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                    'icon' => $cat['icon'],
                    'banner_path' => $cat['banner_path'],
                    'is_active' => true,
                    'sort_order' => $cat['sort_order'],
                ]
            );
        }
    }
}
