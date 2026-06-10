<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\WalletResource;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use ResponseFormat;
    /**
     * Récupérer le profil de l'utilisateur connecté (Utile pour Quasar au refresh)
     */
    public function me(Request $request)
    {
        try {
            //code...

        /** @var User $user */
        $user = $request->user();

        // 1. On charge les COMPTEURS (Stats)
        // Les noms doivent correspondre exactement aux noms de tes méthodes de relations dans User.php
        $user->loadCount([
            'products',
            'ordersAsBuyer',
            'ordersAsSeller',
            'merchantProfiles',
            'serviceRequestsAsProviderOrTransporter'
        ]);

        // 2. On charge les RELATIONS (Objets)
        $user->load(['country', 'preferredCurrency', 'wallet', 'merchantProfiles','roles']);

        return $this->ResponseOk('Profil récupéré', new UserResource($user));
        } catch (\Throwable $th) {
            Log::error('Erreur lors de la récupération du profil utilisateur: ' . $th->getMessage());
            throw $th;
//
        }
    }

    /**
     * Affiche le portefeuille de l'utilisateur
     * wallet -> api.users.wallet
     */
    public function wallet(User $user)
    {
        // On récupère le wallet principal défini dans ton modèle (HasOne)
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json(['message' => 'Portefeuille non trouvé'], 404);
        }

        // On charge la devise associée au wallet
        $wallet->load('currency');

        return new WalletResource($wallet);
    }
}
