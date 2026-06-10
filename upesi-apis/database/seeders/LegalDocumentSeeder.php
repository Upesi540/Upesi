<?php
// database/seeders/LegalDocumentSeeder.php

namespace Database\Seeders;

use App\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Conditions générales d'utilisation (terms)
        LegalDocument::updateOrCreate(
            ['slug' => 'terms'],
            [
                'title' => 'Conditions générales d\'utilisation',
                'slug' => 'terms',
                'version' => '1.0.0',
                'is_active' => true,
                'content' => [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Conditions Générales d\'Utilisation']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Dernière mise à jour : ' . date('d/m/Y')]]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '1. Acceptation des conditions']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'En accédant et en utilisant la plateforme Upesi, vous acceptez d\'être lié par les présentes conditions générales d\'utilisation. Si vous n\'acceptez pas ces conditions, veuillez ne pas utiliser nos services.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '2. Description des services']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Upesi met en relation des producteurs agricoles, des prestataires de services, des fournisseurs et des acheteurs via une plateforme numérique. Nous ne sommes pas partie prenante aux transactions directes entre utilisateurs.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '3. Comptes utilisateur']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Pour utiliser certaines fonctionnalités, vous devez créer un compte. Vous êtes responsable de la confidentialité de vos identifiants et de toutes les activités effectuées sous votre compte.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '4. Transactions et paiements']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Les transactions sont sécurisées via notre système de wallet. Les frais de service sont clairement indiqués avant toute validation.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '5. Propriété intellectuelle']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Tous les contenus de la plateforme (textes, logos, images) sont protégés par les droits d\'auteur.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '6. Résiliation']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Nous nous réservons le droit de suspendre ou résilier tout compte en cas de violation des présentes conditions.']]],
                    ]
                ]
            ]
        );

        // 2. Politique de confidentialité (privacy)
        LegalDocument::updateOrCreate(
            ['slug' => 'privacy'],
            [
                'title' => 'Politique de confidentialité',
                'slug' => 'privacy',
                'version' => '1.0.0',
                'is_active' => true,
                'content' => [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Politique de Confidentialité']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Dernière mise à jour : ' . date('d/m/Y')]]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '1. Collecte des informations']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Nous collectons les informations que vous nous fournissez directement (nom, email, téléphone, adresse) ainsi que les informations d\'utilisation (IP, type d\'appareil).']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '2. Utilisation des données']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Vos données sont utilisées pour : fournir nos services, traiter les transactions, vous informer des mises à jour, et améliorer la plateforme.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '3. Partage des données']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Nous ne vendons pas vos données personnelles. Elles peuvent être partagées avec les prestataires de services uniquement dans le cadre de l\'exécution de nos services.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '4. Sécurité']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données.']]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => '5. Vos droits']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Vous pouvez accéder, rectifier ou supprimer vos données personnelles en nous contactant.']]],
                    ]
                ]
            ]
        );

        // 3. Modèles de contrat (contracts)
        LegalDocument::updateOrCreate(
            ['slug' => 'contracts'],
            [
                'title' => 'Modèles de contrat pour les services Upesi',
                'slug' => 'contracts',
                'version' => '1.0.0',
                'is_active' => true,
                'content' => [
                    'type' => 'doc',
                    'content' => [
                        ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Modèles de Contrat – Prestations et Transport']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Dernière mise à jour : ' . date('d/m/Y')]]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Contrat de Prestation Agricole']]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Entre le client (ci-après "le Demandeur") et le prestataire (ci-après "le Prestataire"), il est convenu ce qui suit :']]],
                        ['type' => 'orderedList', 'attrs' => ['order' => 1], 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Description du service : (à compléter)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Durée : (à compléter)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Prix : (à compléter)']]]]],
                        ]],
                        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Contrat de Transport' ]]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Entre l\'expéditeur et le transporteur :']]],
                        ['type' => 'orderedList', 'attrs' => ['order' => 1], 'content' => [
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Lieu de chargement : (à préciser)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Lieu de livraison : (à préciser)']]]]],
                            ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Date de transport : (à convenir)']]]]],
                        ]],
                        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Ces modèles sont fournis à titre indicatif. Upesi n\'est pas responsable de l\'utilisation qui en est faite.']]],
                    ]
                ]
            ]
        );
    }
}
