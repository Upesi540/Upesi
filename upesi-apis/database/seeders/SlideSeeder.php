<?php

namespace Database\Seeders;

use App\Models\Slide;
use Illuminate\Database\Seeder;

class SlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Bienvenue sur Upesi votre bourse agricole africaine.',
                'sub_title' => 'La référence du marché agricole',
                'image_path' => 'slides/slide1.webp',
                'button_text' => 'Découvrir',
                'link_type' => 'market',
                // 'link_url' => 'marche-intrants', // Exemple de slug
                'button_color' => ' #FF9100',
                'button_text_color' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Équipements Modernes',
                'sub_title' => 'Boostez votre productivité',
                'image_path' => 'slides/slide2.webp',
                'button_text' => 'Voir les outils',
                'link_type' => 'category',
                // 'link_url' => 'equipements-agricoles',
                'button_color' => '#00712D',
                'button_text_color' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Vente en Gros',
                'sub_title' => 'Matières premières de qualité',
                'image_path' => 'slides/slide3.webp',
                'button_text' => 'Explorer',
                'link_type' => 'external',
                // 'link_url' => 'https://upesi-bourse.com/raw-materials',
                'button_color' => ' #FF9100',
                'button_text_color' => '#ffffff',
                'is_active' => true,
            ],
            [
                'title' => 'Agro-Alimentaire',
                'sub_title' => 'Du champ à l\'assiette',
                'image_path' => 'slides/slide4.webp',
                'button_text' => 'Nos produits',
                'link_type' => 'category',
                // 'link_url' => 'agro-alimentaire',
                'button_color' => '#00712D',
                'button_text_color' => '#ffffff',
                'is_active' => true,
            ],
        ];

        foreach ($slides as $slide) {
            // On cherche par le titre, s'il n'existe pas, on le crée avec les données
            Slide::updateOrCreate(
                ['title' => $slide['title']], // Critère de recherche
                $slide                         // Données à insérer ou mettre à jour
            );
        }
    }
}
