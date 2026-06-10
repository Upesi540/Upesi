<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Prix du Marché',
                'icon' => 'banknotes', // On stocke juste le nom pour Flutter/Quasar
                'color' => '#10b981', // Vert émeraude
                'sort_order' => 1,
            ],
            [
                'name' => 'Météo Agricole',
                'icon' => 'cloud',
                'color' => '#0ea5e9', // Bleu ciel
                'sort_order' => 2,
            ],
            [
                'name' => 'Logistique & Transport',
                'icon' => 'truck',
                'color' => '#f59e0b', // Ambre/Orange
                'sort_order' => 3,
            ],
            [
                'name' => 'Alertes Sanitaires',
                'icon' => 'megaphone',
                'color' => '#ef4444', // Rouge
                'sort_order' => 4,
            ],
            [
                'name' => 'Conseils & Formations',
                'icon' => 'academic-cap',
                'color' => '#8b5cf6', // Violet
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            NewsCategory::firstOrCreate(
                ['slug' => Str::slug($category['name'])], // Évite les doublons si tu relances le seeder
                [
                    'name' => $category['name'],
                    'icon' => $category['icon'],
                    'color' => $category['color'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
