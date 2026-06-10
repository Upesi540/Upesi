<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    use ResponseFormat;

    public function store(Request $request): JsonResponse
    {
        // Validation avec first_name et last_name
        $request->validate([
            'identifier' => ['required', 'string'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Déterminer le type d'identifiant
        $isEmail = filter_var($request->identifier, FILTER_VALIDATE_EMAIL);

        // Vérifier unicité
        $field = $isEmail ? 'email' : 'phone';
        if (User::where($field, $request->identifier)->exists()) {
            $message = $isEmail ? 'Cet email est déjà utilisé' : 'Ce numéro est déjà utilisé';
            return $this->ResponseError($message, 422);
        }

        // Créer l'utilisateur
        $user = User::create([
            $field => $request->identifier,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => Hash::make($request->password),
            // currency sera 'XOF' par défaut (défini dans le modèle)
        ]);

        $user->assignRole('customer');
        event(new Registered($user));
        Auth::login($user);

        return $this->ResponseOk('Création de compte réussie', [
            'user' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
        ]);
    }
}
