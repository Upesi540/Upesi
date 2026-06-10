<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LocationController extends Controller
{
    use ResponseFormat;
    /**
     * Récupérer tous les pays (sans leurs états pour éviter la surcharge)
     */
    public function getCountries(Request $request)
    {
        // Cache de 24h car les pays changent rarement
        $countries = Cache::remember('countries_list', 86400, function () {
            return Country::select('id', 'name', 'iso2', 'iso3', 'phone_code', 'emoji', 'currency')
                ->orderBy('name')
                ->get();
        });

        return $this->ResponseOk(
            'Liste des pays récupérée avec succès',
            $countries
        );
    }

    /**
     * Récupérer les états d'un pays spécifique (chargement lazy)
     * C'est plus performant car on charge uniquement quand nécessaire
     */
    public function getStatesByCountry(Request $request, $countryId)
    {
        // Cache de 24h pour les états d'un pays
        $cacheKey = "country_states_{$countryId}";

        $states = Cache::remember($cacheKey, 86400, function () use ($countryId) {
            return State::where('country_id', $countryId)
                ->select('id', 'name', 'iso2', 'country_id')
                ->orderBy('name')
                ->get();
        });

        return $this->ResponseOk(
            'Liste des états récupérée avec succès',
            $states
        );
    }

    /**
     * OPTIONNEL: Récupérer un pays avec ses états (à utiliser avec parcimonie)
     * Pour les cas où vous avez vraiment besoin des deux en une requête
     */
    public function getCountryWithStates(Request $request, $countryId)
    {
        // Cache plus court car c'est une requête plus lourde
        $cacheKey = "country_with_states_{$countryId}";

        $country = Cache::remember($cacheKey, 3600, function () use ($countryId) {
            return Country::with(['states' => function($query) {
                $query->select('id', 'name', 'iso2', 'country_id')
                      ->orderBy('name');
            }])->select('id', 'name', 'iso2', 'iso3', 'emoji')
              ->find($countryId);
        });

        if (!$country) {
            return $this->ResponseNotFound('Pays non trouvé');
        }

        return $this->ResponseOk(
            'Pays avec ses états récupéré avec succès',
            $country
        );
    }

    /**
     * Version simplifiée pour le frontend (recommandée)
     * Retourne les pays ET les états du pays sélectionné en une seule requête
     * Mais les états sont limités à un seul pays
     */
    public function getLocations(Request $request)
    {
        $countryId = $request->get('country_id');

        // Toujours récupérer tous les pays (peu nombreux)
        $countries = Cache::remember('countries_list', 86400, function () {
            return Country::select('id', 'name', 'emoji')->orderBy('name')->get();
        });

        $states = [];
        if ($countryId) {
            // Ne charger les états que si un pays est sélectionné
            $states = Cache::remember("country_states_{$countryId}", 86400, function () use ($countryId) {
                return State::where('country_id', $countryId)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
            });
        }

        return $this->ResponseOk('Données de localisation', [
            'countries' => $countries,
            'states' => $states
        ]);
    }
}
