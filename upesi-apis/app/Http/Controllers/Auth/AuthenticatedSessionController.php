<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Observers\UserObserver;
use App\Traits\ResponseFormat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AuthenticatedSessionController extends Controller
{
    use ResponseFormat;

    /**
     * Connexion Universelle (Web & Mobile) via Token.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // 1. Valide les identifiants
        $request->authenticate();

        /** @var User $user */
        $user = Auth::user();

        // 3. On génère le token Sanctum (L'annotation @var User règle le soulignement)
        $token = $user->createToken('auth_token')->plainTextToken;


        // Notifier la connexion
        $observer = new UserObserver();
        $observer->sendLoginNotification(
            auth()->user(),
            request()->ip(),
            request()->userAgent(),
            false
        );
        // 4. On renvoie la réponse formatée avec la ressource
        return $this->ResponseOk('Connexion réussie', [
            'user'       => new UserResource($user),
            'token'      => $token,
            'token_type' => 'Bearer',
        ]);
    }
    public function generateMagicLink(Request $request)
    {
        $user = $request->user();
        $destination = $request->input('destination', '/app');
        $origin = $request->input('origin', 'web'); // 'web' ou 'mobile'

        $token = Str::random(64);

        Cache::put('magic_token_' . $token, [
            'user_id' => $user->id,
            'redirect_to' => $destination,
            'origin' => $origin
        ], 60);

        $url = URL::temporarySignedRoute(
            'autologin',
            now()->addMinutes(2),
            ['token' => $token]
        );

        return $this->ResponseOk('Lien généré', ['url' => $url]);
    }

    public function autologin(Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Lien expiré ou invalide.');
        }

        $token = $request->query('token');
        $data = Cache::pull('magic_token_' . $token);

        if (!$data) {
            return redirect('/login')->with('error', 'Lien déjà utilisé ou expiré.');
        }

        // --- LA CORRECTION EST ICI ---
        // Si un utilisateur est déjà connecté (même un ancien), on le déconnecte proprement
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate(); // Détruit la session précédente
            $request->session()->regenerateToken(); // Sécurité anti-CSRF
        }

        // Connexion du nouvel utilisateur (celui du lien magique)
        Auth::loginUsingId($data['user_id']);

        // On régénère la session pour le nouvel utilisateur
        $request->session()->regenerate();

        // Sauvegarde de l'origine
        session(['quasar_origin' => $data['origin']]);

        // Redirection dynamique
        return redirect($data['redirect_to']);
    }
    /**
     * Déconnexion (Supprime le token actuel).
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->ResponseOk('Déconnexion réussie');
    }
}
