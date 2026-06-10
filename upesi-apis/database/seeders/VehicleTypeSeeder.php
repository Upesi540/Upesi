<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Moto', 'slug' => 'moto', 'icon' => 'motorcycle', 'sort_order' => 1],
            ['name' => 'Tricycle', 'slug' => 'tricycle', 'icon' => 'tricycle', 'sort_order' => 2],
            ['name' => 'Voiture', 'slug' => 'voiture', 'icon' => 'car', 'sort_order' => 3],
            ['name' => 'Camion', 'slug' => 'camion', 'icon' => 'truck', 'sort_order' => 4],
            ['name' => 'Titan', 'slug' => 'titan', 'icon' => 'truck', 'sort_order' => 5],
            ['name' => 'Bateau', 'slug' => 'bateau', 'icon' => 'boat', 'sort_order' => 6],
            ['name' => 'Avion', 'slug' => 'avion', 'icon' => 'plane', 'sort_order' => 7],
        ];

        foreach ($types as $type) {
            VehicleType::updateOrCreate(
                ['slug' => $type['slug']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'icon' => $type['icon'],
                    'sort_order' => $type['sort_order'],
                ]
            );
        }
    }
}
