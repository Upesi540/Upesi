<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Ministère de l\'Agriculture du Togo',
                'short_description' => 'Soutien institutionnel et stratégique pour le développement agricole.',
                'description' => 'Le Ministère de l\'Agriculture, de l\'Élevage et de la Pêche du Togo accompagne Upesi dans la modernisation des chaînes de valeur agricoles à travers le pays.',
                'website_url' => 'https://agriculture.gouv.tg',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => null,
                'type' => 'institutional',
                'level' => 'platinum',
                'show_on_home' => true,
                'sort_order' => 10,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'Ecobank Togo',
                'short_description' => 'Partenaire financier pour l\'inclusion des producteurs.',
                'description' => 'Facilitateur de transactions et partenaire de crédit pour les coopératives agricoles utilisant notre bourse intelligente.',
                'website_url' => 'https://www.ecobank.com/tg',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/ecobanktogo',
                'type' => 'financial',
                'level' => 'gold',
                'show_on_home' => true,
                'sort_order' => 20,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'Tech Hub Libreville',
                'short_description' => 'Accompagnement technique et innovation digitale.',
                'description' => 'Accélérateur de solutions technologiques locales spécialisé dans l\'AgriTech et le déploiement de solutions NoSQL/SQL performantes.',
                'website_url' => 'https://techhub-libreville.com',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/techhublibreville',
                'type' => 'technical',
                'level' => 'silver',
                'show_on_home' => false,
                'sort_order' => 30,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'AGRA (Alliance for a Green Revolution in Africa)',
                'short_description' => 'Soutien à la transformation agricole en Afrique.',
                'description' => 'AGRA œuvre pour une révolution verte durable en Afrique en soutenant les petits exploitants agricoles et les écosystèmes agroalimentaires.',
                'website_url' => 'https://agra.org',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/AGRA',
                'type' => 'institutional',
                'level' => 'platinum',
                'show_on_home' => true,
                'sort_order' => 5,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'NSIA Banque',
                'short_description' => 'Partenaire bancaire pour le financement agricole.',
                'description' => 'NSIA Banque propose des solutions de crédit adaptées aux coopératives et aux producteurs via la plateforme Upesi.',
                'website_url' => 'https://www.nsia.ci',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/NSIABanque',
                'type' => 'financial',
                'level' => 'gold',
                'show_on_home' => true,
                'sort_order' => 25,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'African Development Bank (AfDB)',
                'short_description' => 'Financement de projets agricoles structurants.',
                'description' => 'La Banque Africaine de Développement soutient l\'expansion d\'Upesi à travers des programmes d\'investissement dans les infrastructures numériques rurales.',
                'website_url' => 'https://www.afdb.org',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/AfDBGroup',
                'type' => 'financial',
                'level' => 'platinum',
                'show_on_home' => false,
                'sort_order' => 15,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'IFDC (International Fertilizer Development Center)',
                'short_description' => 'Expertise technique en fertilisation et agronomie.',
                'description' => 'L\'IFDC apporte son savoir-faire en matière de gestion des sols et de fertilisation raisonnée aux producteurs utilisant Upesi.',
                'website_url' => 'https://ifdc.org',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/IFDC',
                'type' => 'technical',
                'level' => 'silver',
                'show_on_home' => true,
                'sort_order' => 40,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'FAO Togo',
                'short_description' => 'Appui institutionnel et technique.',
                'description' => 'L\'Organisation des Nations Unies pour l\'alimentation et l\'agriculture collabore avec Upesi pour la formation des producteurs et la sécurité alimentaire.',
                'website_url' => 'https://www.fao.org/togo',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/FAO',
                'type' => 'institutional',
                'level' => 'gold',
                'show_on_home' => false,
                'sort_order' => 35,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'Orange Digital Center',
                'short_description' => 'Accompagnement numérique et innovation.',
                'description' => 'Le Orange Digital Center met à disposition ses infrastructures et programmes de formation pour accélérer la transformation digitale des agriculteurs.',
                'website_url' => 'https://digitalcenter.orange.com',
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => 'https://facebook.com/OrangeDigitalCenter',
                'type' => 'technical',
                'level' => 'bronze',
                'show_on_home' => false,
                'sort_order' => 45,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
            [
                'name' => 'Coopérative des producteurs de café du Cameroun',
                'short_description' => 'Partenaire coopératif pour la commercialisation.',
                'description' => 'Cette coopérative regroupe plus de 2 000 producteurs de café et utilise Upesi pour vendre leurs récoltes sur les marchés nationaux et internationaux.',
                'website_url' => null,
                'logo_path' => null,
                'cover_image' => null,
                'facebook_url' => null,
                'type' => 'commercial',  // Type valide : 'commercial' au lieu de 'cooperative'
                'level' => 'silver',
                'show_on_home' => true,
                'sort_order' => 50,
                'internal_contact_name' => null,
                'internal_contact_email' => null,
            ],
        ];

        foreach ($partners as $partnerData) {
            Partner::updateOrCreate(
                ['name' => $partnerData['name']],
                [
                    'slug' => Str::slug($partnerData['name']),
                    'short_description' => $partnerData['short_description'],
                    'description' => $partnerData['description'],
                    'website_url' => $partnerData['website_url'],
                    'logo_path' => $partnerData['logo_path'],
                    'cover_image' => $partnerData['cover_image'],
                    'facebook_url' => $partnerData['facebook_url'],
                    'type' => $partnerData['type'],
                    'level' => $partnerData['level'],
                    'show_on_home' => $partnerData['show_on_home'],
                    'sort_order' => $partnerData['sort_order'],
                    'internal_contact_name' => $partnerData['internal_contact_name'],
                    'internal_contact_email' => $partnerData['internal_contact_email'],
                    'is_active' => true,
                ]
            );

            $this->command->info("✓ Partenaire traité : {$partnerData['name']}");
        }

        $this->command->info("✅ Seeder des partenaires terminé avec succès (10 partenaires).");
    }
}
