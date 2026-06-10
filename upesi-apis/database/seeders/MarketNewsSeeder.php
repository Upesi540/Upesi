<?php

namespace Database\Seeders;

use App\Models\MarketNews;
use App\Models\NewsCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketNewsSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::first();

        $categories = NewsCategory::all();
        if ($categories->isEmpty()) {
            $this->call(NewsCategorySeeder::class);
            $categories = NewsCategory::all();
        }

        $types = ['flash', 'news', 'article', 'alert'];
        $priorities = ['low', 'normal', 'high', 'urgent'];

        $titles = [
            'Le prix du cacao atteint un record historique',
            'Nouvelle formation gratuite pour les producteurs de maïs',
            'Lancement d’une plateforme de vente directe au Bénin',
            'Les pluies tardives menacent la récolte de coton',
            'Crise du transport : les coopératives s’organisent',
            'Subventions aux intrants : ce qui change en 2025',
            'Comment l’IA aide à prévoir les prix des légumes',
            'Le Sénégal lance son programme d’irrigation connectée',
            'Exportation de l’anacarde : les défis logistiques',
            'Agriculture biologique : un marché porteur en Afrique',
            'Bientôt une bourse des céréales à Abidjan',
            'Les jeunes agriculteurs s’emparent du digital',
            'Pénurie d’engrais : les alternatives locales',
            'Record d’exportation de café vers l’Europe',
            'Lutte contre les criquets : opération réussie au Sahel',
        ];

        $images = [
            'slides/slide2.webp',
            'slides/slide2.webp',
            'slides/slide2.webp',
            null,
        ];

        for ($i = 1; $i <= 25; $i++) {
            $title = $titles[array_rand($titles)] . ' - ' . $i;
            $type = $types[array_rand($types)];
            $priority = $priorities[array_rand($priorities)];
            $isPinned = ($i <= 3);
            $category = $categories->random();

            $publishedAt = now()->subDays(rand(0, 365))->subHours(rand(0, 24));
            $expiresAt = (rand(1, 100) <= 30) ? $publishedAt->copy()->addDays(30) : null;

            // Contenu TipTap en tableau PHP (sera encodé en JSON)
            $contentArray = [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 2],
                        'content' => [['type' => 'text', 'text' => $title]]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => fake()->paragraph(3)]
                        ]
                    ],
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 3],
                        'content' => [['type' => 'text', 'text' => 'Points clés']]
                    ],
                    [
                        'type' => 'bulletList',
                        'content' => array_map(function($item) {
                            return [
                                'type' => 'listItem',
                                'content' => [
                                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $item]]]
                                ]
                            ];
                        }, fake()->sentences(rand(2, 4)))
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => fake()->sentence(10)]
                        ]
                    ]
                ]
            ];

            // Convertir en JSON string (car la colonne `content` est text, pas json)
            // $contentJson = json_encode($contentArray);

            $excerpt = fake()->sentence(12);
            $tags = fake()->randomElements(['cacao', 'café', 'maïs', 'riz', 'engrais', 'digital', 'export', 'formation', 'pluie', 'récolte'], rand(1, 4));

            // Meta données
            $metaData = [
                'title' => $title,
                'description' => $excerpt,
                'keywords' => implode(',', $tags),
            ];

            MarketNews::updateOrCreate(
                ['slug' => Str::slug($title) . '-' . uniqid()],
                [
                    'title' => $title,
                    'content' => $contentArray,
                    'excerpt' => $excerpt,
                    'featured_image' => $images[array_rand($images)],
                    'meta_data' => $metaData,
                    'type' => $type,
                    'priority' => $priority,
                    'author_id' => $author->id,
                    'news_category_id' => $category->id,
                    'tags' => $tags,
                    'published_at' => $publishedAt,
                    'expires_at' => $expiresAt,
                    'is_pinned' => $isPinned,
                    'is_active' => true,
                ]
            );

            $this->command->info("✓ Actualité créée : $title");
        }

        $this->command->info("✅ 25 actualités créées avec succès.");
    }
}
