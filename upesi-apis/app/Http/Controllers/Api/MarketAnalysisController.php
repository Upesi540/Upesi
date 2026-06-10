<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\PriceHistory;
use App\Services\MarketAnalysisService;
use App\Traits\ResponseFormat; // Import du trait
use Illuminate\Http\Request;

class MarketAnalysisController extends Controller
{
    use ResponseFormat; // Utilisation du trait

    protected $service;

    public function __construct(MarketAnalysisService $service)
    {
        $this->service = $service;
    }

    public function ticker(Request $request)
    {
        // 1. Détermination du Country ID (Manuel > IP > Fallback)
        $countryId = $request->input('country_id');

        if (!$countryId) {
            try {
                $location = geoip($request->ip());
                $country = Country::where('iso2', $location->iso_code)->first();
                $countryId = $country?->id;
            } catch (\Exception $e) {
                $countryId = null;
            }
        }

        // 2. Vérification de la présence de données pour ce pays
        $hasPrices = PriceHistory::where('country_id', $countryId)->exists();

        if (!$countryId || !$hasPrices) {
            $countryId = Country::where('iso2', 'TG')->first()?->id
                ?? Country::first()?->id;
        }

        // 3. Récupération des données via le service (Déjà optimisé Anti N+1)
        $trends = $this->service->getTickerTrendsByCountry($countryId);

        // 4. Préparation du payload final
        $data = [
            'country_id'  => $countryId,
            'is_detected' => $request->missing('country_id'),
            'trends'      => $trends,
            'data_count'=>count($trends)
        ];

        // 5. Retour via le trait ResponseFormat
        return $this->ResponseOk('market_ticker', $data);
    }
}
