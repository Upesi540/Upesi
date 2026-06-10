<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Market;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Désactiver les événements
        Category::unsetEventDispatcher();

        // Récupérer les marchés
        $marcheIntrants = Market::where('name', 'Intrants Agricole')->first();
        $marcheMatieres = Market::where('name', 'Matières Premières Agricole')->first();
        $marcheAgro = Market::where('name', 'Agro-Alimentaire')->first();

        if (!$marcheIntrants || !$marcheMatieres || !$marcheAgro) {
            $this->command->error('❌ Veuillez d\'abord exécuter le MarketSeeder !');
            return;
        }

        $categories = [
            // ========== MARCHÉ INTRANTS AGRICOLE ==========
            [
                'market_id' => $marcheIntrants->id,
                'name' => 'Semences & Plants',
                'description' => 'Semences certifiées, plants maraîchers, boutures et matériel végétal',
                'icon' => '🌱',
                'sort_order' => 10,
            ],
            [
                'market_id' => $marcheIntrants->id,
                'name' => 'Fertilisants',
                'description' => 'Engrais minéraux, organiques et amendements',
                'icon' => '🧪',
                'sort_order' => 20,
            ],
            [
                'market_id' => $marcheIntrants->id,
                'name' => 'Protection des cultures',
                'description' => 'Pesticides, herbicides, fongicides et produits phytosanitaires',
                'icon' => '🛡️',
                'sort_order' => 30,
            ],
            [
                'market_id' => $marcheIntrants->id,
                'name' => 'Matériel agricole',
                'description' => 'Outils, équipements et machines agricoles',
                'icon' => '🚜',
                'sort_order' => 40,
            ],
            [
                'market_id' => $marcheIntrants->id,
                'name' => 'Intrants élevage',
                'description' => 'Aliments pour bétail et produits vétérinaires',
                'icon' => '🐄',
                'sort_order' => 50,
            ],

            // ========== MARCHÉ MATIÈRES PREMIÈRES AGRICOLE ==========
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Céréales',
                'description' => 'Céréales en grain pour consommation humaine et animale',
                'icon' => '🌾',
                'sort_order' => 10,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Légumineuses',
                'description' => 'Graines de légumineuses sèches',
                'icon' => '🫘',
                'sort_order' => 20,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Fruits',
                'description' => 'Fruits frais de saison',
                'icon' => '🍎',
                'sort_order' => 30,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Légumes',
                'description' => 'Légumes frais de saison',
                'icon' => '🥕',
                'sort_order' => 40,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Légumes racines & tubercules',
                'description' => 'Tubercules et racines frais',
                'icon' => '🥔',
                'sort_order' => 50,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Produits d\'élevage',
                'description' => 'Animaux vivants et produits animaux bruts',
                'icon' => '🐓',
                'sort_order' => 60,
            ],
            [
                'market_id' => $marcheMatieres->id,
                'name' => 'Produits d\'exportation',
                'description' => 'Produits de rente pour l\'export',
                'icon' => '🚢',
                'sort_order' => 70,
            ],

            // ========== MARCHÉ AGRO-ALIMENTAIRE ==========
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Farines & Semoules',
                'description' => 'Produits céréaliers transformés',
                'icon' => '🍚',
                'sort_order' => 10,
            ],
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Huiles & Corps gras',
                'description' => 'Huiles végétales et beurres',
                'icon' => '🫒',
                'sort_order' => 20,
            ],
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Produits fermentés',
                'description' => 'Produits transformés par fermentation',
                'icon' => '⚗️',
                'sort_order' => 30,
            ],
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Fruits & Légumes transformés',
                'description' => 'Fruits et légumes séchés, en purée',
                'icon' => '🥭',
                'sort_order' => 40,
            ],
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Boissons & Jus',
                'description' => 'Jus de fruits et boissons traditionnelles',
                'icon' => '🧃',
                'sort_order' => 50,
            ],
            [
                'market_id' => $marcheAgro->id,
                'name' => 'Produits bio & spécialités',
                'description' => 'Produits certifiés bio et spécialités',
                'icon' => '🌿',
                'sort_order' => 60,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                [
                    'market_id' => $categoryData['market_id'],
                    'name' => $categoryData['name'],
                ],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                    'icon' => $categoryData['icon'],
                    'sort_order' => $categoryData['sort_order'],
                    'meta_title' => $categoryData['name'] . ' - ' . $this->getMarketName($categoryData['market_id']),
                    'meta_description' => $categoryData['description'],
                    'is_active' => true,
                    'parent_id' => null,
                ]
            );
        }

        $this->command->info('✅ Catégories synchronisées : ' . Category::count() . ' dans ' . Market::count() . ' marchés');
    }

    private function getMarketName($marketId): string
    {
        $market = Market::find($marketId);
        return $market ? $market->name : 'Bourse Agricole';
    }
}
