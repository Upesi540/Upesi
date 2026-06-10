<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin' => [
                'display_name' => 'Administrateur Système',
                'description' => 'Accès total à la plateforme.'
            ],
            'admin' => [
                'display_name' => 'Gestionnaire',
                'description' => 'Gestion quotidienne et supervision.'
            ],
            'market_reporter' => [
                'display_name' => 'Journal de la Bourse',
                'description' => 'Publication des indicateurs et tendances de marché.'
            ],
            'merchant' => [
                'display_name' => 'Marchand',
                'description' => 'Rôle global pour tout vendeur (producteur, fournisseur, négociant, etc.) ayant accès au tableau de bord commercial.'
            ],
            'customer' => [
                'display_name' => 'Client',
                'description' => 'Utilisateur final accédant aux marchés pour effectuer des achats.'
            ],
        ];

        foreach ($roles as $name => $data) {
            Role::updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                [
                    'display_name' => $data['display_name'],
                    'description' => $data['description'],
                ]
            );
        }
    }
}
