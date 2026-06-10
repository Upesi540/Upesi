<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Types de projets
        $projectTypes = [
            'Plateforme de mise en marché',
            'Programme de formation agricole',
            'Projet d’irrigation intelligent',
            'Extension numérique rurale',
            'Programme de résilience climatique',
            'Labo d’innovation agritech',
            'Projet de certification bio',
            'Programme d’accès au crédit',
            'Projet de stockage post-récolte',
            'Programme de transformation locale',
            'Projet de traçabilité blockchain',
            'Programme de lutte antiparasitaire',
            'Projet de fermes verticales',
            'Programme d’énergie solaire agricole',
            'Projet de marché digital de gros',
            'Programme de coaching entrepreneurial',
            'Projet de cartographie des sols',
            'Programme de labellisation AOP',
            'Projet de plateforme logistique',
            'Programme d’assurance agricole'
        ];

        $clients = [
            'Ministère de l\'Agriculture', 'Union Européenne', 'AFD', 'Banque Mondiale',
            'Coopérative nationale', 'Chambre d\'Agriculture', 'Programme Alimentaire Mondial',
            'IFAD', 'USAID', 'GIZ', 'FAO', 'Agence Française de Développement',
            'Banque Africaine de Développement', 'PNUD', 'Mastercard Foundation', 'Rockefeller Foundation',
            'Coopérative des producteurs', 'Institut de recherche agronomique', 'Agence nationale de développement',
            'ONG locale'
        ];

        $locations = [
            'Côte d\'Ivoire', 'Sénégal', 'Mali', 'Burkina Faso', 'Bénin', 'Ghana',
            'Togo', 'Nigéria', 'Cameroun', 'Gabon', 'RDC', 'Kenya', 'Tanzanie',
            'Ouganda', 'Rwanda', 'Zambie', 'Mozambique', 'Madagascar', 'Éthiopie', 'Afrique du Sud'
        ];

        $statuses = ['ongoing', 'completed', 'planned'];
        $images = ['slides/slide1.webp', 'slides/slide2.webp', 'slides/slide3.webp', null];
        $gallery = [
            ['slides/slide1.webp'],
            ['slides/slide1.webp', 'slides/slide2.webp'],
            ['slides/slide1.webp', 'slides/slide2.webp', 'slides/slide3.webp'],
            []
        ];

        $created = 0;
        $skipped = 0;

        for ($i = 1; $i <= 20; $i++) {
            $typeIndex = ($i - 1) % count($projectTypes);
            $title = $projectTypes[$typeIndex] . ' - ' . ($i < 10 ? '0' . $i : $i);

            // Éviter les doublons de titre
            if (Project::where('title', $title)->exists()) {
                $skipped++;
                continue;
            }

            $startDate = now()->subMonths(rand(1, 24));
            $endDate = (rand(0, 1) == 1) ? $startDate->copy()->addMonths(rand(3, 12)) : null;
            $status = $statuses[array_rand($statuses)];

            // Si le projet est terminé, on force une end_date
            if ($status === 'completed' && !$endDate) {
                $endDate = $startDate->copy()->addMonths(rand(3, 12));
            }

            // Description structurée en JSON TipTap
            $description = [
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 3],
                        'content' => [['type' => 'text', 'text' => 'Contexte du projet']]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Ce projet vise à ' . $this->randomSentence() . ' Il a été lancé en réponse aux besoins exprimés par les producteurs locaux.']
                        ]
                    ],
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 4],
                        'content' => [['type' => 'text', 'text' => 'Objectifs clés']]
                    ],
                    [
                        'type' => 'bulletList',
                        'content' => array_map(function($obj) {
                            return [
                                'type' => 'listItem',
                                'content' => [
                                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $obj]]]
                                ]
                            ];
                        }, $this->randomObjectives())
                    ],
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 4],
                        'content' => [['type' => 'text', 'text' => 'Résultats attendus']]
                    ],
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Les bénéficiaires verront une amélioration significative de leurs revenus et de leur accès aux marchés.']
                        ]
                    ],
                    [
                        'type' => 'heading',
                        'attrs' => ['level' => 4],
                        'content' => [['type' => 'text', 'text' => 'Témoignage']]
                    ],
                    [
                        'type' => 'blockquote',
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'content' => [
                                    ['type' => 'text', 'text' => '« ' . $this->randomTestimonial() . ' »']
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            // Données de témoignages (peuvent être vides)
            $hasTestimonials = rand(0, 1);
            $testimonials = $hasTestimonials ? [
                [
                    'customer_name' => $this->randomName(),
                    'customer_role' => 'Producteur',
                    'content' => $this->randomTestimonial(),
                    'rating' => rand(4, 5)
                ],
                [
                    'customer_name' => $this->randomName(),
                    'customer_role' => 'Coopérative',
                    'content' => $this->randomTestimonial(),
                    'rating' => rand(4, 5)
                ]
            ] : [];

            $projectData = [
                'title' => $title,
                'slug' => Str::slug($title),
                'description' => $description,
                'client' => $clients[array_rand($clients)],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate ? $endDate->toDateString() : null,
                'status' => $status,
                'location' => $locations[array_rand($locations)],
                'image_path' => $images[array_rand($images)],
                'gallery' => $gallery[array_rand($gallery)],
                'testimonials' => $testimonials,
                'sort_order' => $i * 10,
                'is_active' => true,
            ];

            Project::create($projectData);
            $created++;
            $this->command->info("✓ Projet créé: {$title}");
        }

        $this->command->info("✅ {$created} projets créés, {$skipped} projets existants conservés.");
    }

    // Helper pour générer des phrases aléatoires
    private function randomSentence(): string
    {
        $sentences = [
            'améliorer la productivité agricole grâce aux technologies numériques.',
            'faciliter l’accès aux marchés pour les petits producteurs.',
            'réduire les pertes post-récolte par des solutions de stockage innovantes.',
            'promouvoir une agriculture durable et résiliente face au climat.',
            'renforcer les compétences des coopératives en gestion financière.',
            'connecter les producteurs aux acheteurs via une plateforme mobile.',
            'développer des chaînes de valeur inclusives pour les femmes rurales.',
            'mettre en place un système de traçabilité des produits agricoles.',
            'former les jeunes à l’agripreneuriat et aux métiers verts.',
            'distribuer des intrants agricoles de qualité à prix subventionnés.'
        ];
        return $sentences[array_rand($sentences)];
    }

    private function randomObjectives(): array
    {
        $objectivesPool = [
            'Former 500 producteurs aux bonnes pratiques agricoles',
            'Installer 10 points de collecte dans les zones rurales',
            'Écouler 300 tonnes de produits sur les marchés urbains',
            'Augmenter les revenus des coopératives de 25%',
            'Réduire les pertes post-récolte de 30%',
            'Créer 50 emplois directs pour les jeunes',
            'Mettre en place un système de crédit mobile',
            'Développer une application de conseil météo',
            'Connecter 1 000 agriculteurs à la plateforme',
            'Obtenir la certification biologique pour 5 coopératives'
        ];
        shuffle($objectivesPool);
        return array_slice($objectivesPool, 0, rand(3, 5));
    }

    private function randomTestimonial(): string
    {
        $testimonials = [
            'Grâce à ce projet, j’ai doublé mes revenus en seulement six mois.',
            'La formation reçue a transformé notre façon de cultiver. Aujourd’hui, nous produisons mieux et vendons plus cher.',
            'Je ne pensais pas qu’un jour je pourrais vendre directement aux supermarchés. C’est une révolution !',
            'L’application est simple à utiliser même pour ceux qui ne savent pas lire. Bravo à toute l’équipe.',
            'Notre coopérative est devenue un modèle dans la région grâce à cet accompagnement.',
            'Les alertes météo nous ont sauvé plusieurs récoltes. Merci !',
            'Je recommande vivement ce programme à tous les producteurs sérieux.',
            'Le service client est réactif et les solutions proposées vraiment adaptées au terrain.'
        ];
        return $testimonials[array_rand($testimonials)];
    }

    private function randomName(): string
    {
        $firstNames = ['Amadou', 'Fatou', 'Ibrahim', 'Aissata', 'Mamadou', 'Oumou', 'Souleymane', 'Mariam', 'Boubacar', 'Aminata'];
        $lastNames = ['Koné', 'Diallo', 'Traoré', 'Cissé', 'Sylla', 'Touré', 'Sow', 'Keita', 'Fofana', 'Camara'];
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }
}
