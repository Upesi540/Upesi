<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import indispensable pour les logs

class EmailVerificationNotificationController extends Controller
{
    /**
     * Renvoie un lien de vérification d'email (Pure API).
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        Log::info('Tentative de renvoi d\'email de vérification', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        // 1. Si l'utilisateur a déjà validé son email
        if ($user->hasVerifiedEmail()) {
            Log::warning('L\'utilisateur a déjà vérifié son email, envoi annulé.', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Email déjà vérifié.',
                'status' => 'already_verified'
            ]);
        }

        try {
            // 2. Envoi de l'email de notification
            Log::info('Appel de sendEmailVerificationNotification()...');

            $user->sendEmailVerificationNotification();

            Log::info('Méthode d\'envoi exécutée avec succès.');

            // 3. Réponse JSON pour Quasar
            return response()->json([
                'message' => 'Lien de vérification envoyé.',
                'status' => 'verification-link-sent'
            ]);

        } catch (\Exception $e) {
            // C'est ici que tu verras les erreurs SMTP (Hostinger, mot de passe, port, etc.)
            Log::error('ÉCHEC de l\'envoi de l\'email de vérification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur technique lors de l\'envoi de l\'email.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
