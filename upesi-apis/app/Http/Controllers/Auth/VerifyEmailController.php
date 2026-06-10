<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        // On définit l'URL de base de ta page de résultat Quasar
        // (Ajoute le # si tu es en mode Hash : https://u-pesi.com/#/email-verified)
        $baseUrl = rtrim($frontendUrl, '/') . '/email-verified';
        // 1. On cherche l'utilisateur. Si pas trouvé -> Erreur sur Quasar
        $user = User::find($request->route('id'));
        if (!$user) {
            return redirect()->away($baseUrl . '?status=error');
        }

        // 2. Vérification de la signature du lien (Hash) -> Erreur sur Quasar
        if (!hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return redirect()->away($baseUrl . '?status=error');
        }

        // 3. Si déjà vérifié -> Succès (ou info) sur Quasar
        if ($user->hasVerifiedEmail()) {
            return redirect()->away($baseUrl . '?status=already_verified');
        }

        // 4. On tente la validation
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            // Succès total -> Quasar affiche l'icône verte
            return redirect()->away($baseUrl . '?status=verified');
        }

        // 5. Cas de secours (si markEmailAsVerified échoue pour X raison)
        return redirect()->away($baseUrl . '?status=error');
    }
}
