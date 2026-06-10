<?php

namespace App\Traits;

use App\Models\User;

trait HasProfileBasedAccess
{
    /**
     * Détermine si l'utilisateur peut accéder à cette ressource.
     *
     * @param array $allowedProfileTypes Types de profils autorisés (ex: ['supplier', 'trader'])
     * @return bool
     */
    protected function canAccessResource(array $allowedProfileTypes = []): bool
    {
        /** @var User $user */
        $user = auth()->user();

        // 1. Les super admins et admins ont toujours accès
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // 2. Les utilisateurs sans profil marchand (clients simples) n'ont pas accès aux ressources marchandes
        if ($user->merchantProfiles()->count() === 0) {
            return false;
        }

        // 3. Vérifier si l'utilisateur possède au moins un profil du type autorisé
        $hasAllowedProfile = $user->merchantProfiles()
            ->whereIn('type', $allowedProfileTypes)
            ->exists();

        return $hasAllowedProfile;
    }
}
