<?php

namespace App\Services;

use App\Models\PriceHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MarketAnalysisService
{
    private function formatTrend(float $current, float $previous): array
    {
        $diff = $current - $previous;
        // Sécurité : si le prix précédent est 0, on ne peut pas calculer de % réel.
        $percentage = ($previous > 0) ? ($diff / $previous) * 100 : 0;

        return [
            'status'     => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable'),
            'percentage' => round(abs($percentage), 1),
            'icon'       => $diff > 0 ? 'heroicon-m-arrow-trending-up' : ($diff < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'),
            'color'      => $diff > 0 ? 'success' : ($diff < 0 ? 'danger' : 'gray'),
        ];
    }

    public function getLocalizedMarketTrend($cityId = null, $stateId = null, $countryId = null): array
    {
        // 1. Créer une clé unique basée sur la zone géographique
        // On utilise "null" si la valeur est vide pour que la clé soit toujours valide
        $cacheKey = "market_overview_c" . ($cityId ?? '0') . "_s" . ($stateId ?? '0') . "_p" . ($countryId ?? '0');

        return Cache::remember($cacheKey, 3600, function () use ($cityId, $stateId, $countryId) {

            // 2. Trouver la dernière date (on ne change pas cette logique)
            $lastDate = PriceHistory::max('recorded_at');
            if (!$lastDate) return $this->formatTrend(0, 0);

            $query = PriceHistory::query();
            if ($cityId) $query->where('city_id', $cityId);
            elseif ($stateId) $query->where('state_id', $stateId);
            elseif ($countryId) $query->where('country_id', $countryId);

            // 3. Calcul du prix actuel
            $currentPrice = (float) (clone $query)->whereDate('recorded_at', $lastDate)->avg('average_price');

            // 4. Calcul du prix précédent (pour la tendance)
            $previousPrice = (float) (clone $query)->whereDate('recorded_at', '<', $lastDate)
                ->latest('recorded_at')
                ->avg('average_price') ?? $currentPrice;

            return $this->formatTrend($currentPrice, $previousPrice);
        });
    }

    public function getLocalizedCropTrend(int $cropId, $cityId = null, $stateId = null, $countryId = null): array
    {
        $cacheKey = "crop_{$cropId}_c{$cityId}_s{$stateId}_p{$countryId}";

        return Cache::remember($cacheKey, 3600, function () use ($cropId, $cityId, $stateId, $countryId) {
            $lastDate = PriceHistory::where('crop_id', $cropId)->max('recorded_at');
            if (!$lastDate) return array_merge(['price' => 0], $this->formatTrend(0, 0));

            $query = PriceHistory::where('crop_id', $cropId);
            if ($cityId) $query->where('city_id', $cityId);
            elseif ($stateId) $query->where('state_id', $stateId);
            elseif ($countryId) $query->where('country_id', $countryId);

            $currentPrice = (float) (clone $query)->whereDate('recorded_at', $lastDate)->avg('average_price');

            // Comparaison avec la période précédente (7 jours avant la dernière date)
            $compareDate = Carbon::parse($lastDate)->subDays(7);
            $oldPrice = (float) (clone $query)->whereDate('recorded_at', '<=', $compareDate)
                ->latest('recorded_at')
                ->avg('average_price') ?? $currentPrice;

            return array_merge(
                ['price' => $currentPrice],
                $this->formatTrend($currentPrice, $oldPrice)
            );
        });
    }

    public function getTickerTrendsByCountry($countryId, int $limit = 15): Collection
    {
        return Cache::remember("api_ticker_country_{$countryId}", 300, function () use ($countryId, $limit) {

            // 1. Récupérer l'ID du dernier prix pour chaque culture
            $latestPriceIds = PriceHistory::where('country_id', $countryId)
                ->selectRaw('MAX(id) as id, SUM(volume_quantity) as total_vol') // On peut trier par volume
                ->groupBy('crop_id')
                ->orderBy('total_vol', 'desc') // Les plus gros volumes en premier
                ->pluck('id');

            if ($latestPriceIds->isEmpty()) return collect();

            // 2. Récupérer les données complètes (Prix, Volume, Unité, Crop)
            // On charge 'unit:id,short_name' pour avoir le symbole (kg, sac, tonne, etc.)
            $currentPrices = PriceHistory::whereIn('id', $latestPriceIds)
                ->with([
                    'crop:id,name',
                    'unit:id,name'
                ])
                ->take($limit)
                ->get();

            // 3. Récupérer les prix d'il y a 7 jours pour la tendance
            $oldPrices = PriceHistory::where('country_id', $countryId)
                ->whereIn('crop_id', $currentPrices->pluck('crop_id'))
                ->whereDate('recorded_at', '<=', now()->subDays(7))
                ->selectRaw('crop_id, AVG(average_price) as old_price')
                ->groupBy('crop_id')
                ->get()
                ->pluck('old_price', 'crop_id');

            return $currentPrices->map(function ($item) use ($oldPrices) {
                $current = (float) $item->average_price;
                $previous = (float) ($oldPrices[$item->crop_id] ?? $current);

                $trend = $this->formatTrend($current, $previous);

                return [
                    'crop_id'      => $item->crop->id,
                    'name'         => $item->crop->name,
                    'price'        => round($current, 2),
                    'volume'       => (float) $item->volume_quantity, // Le volume échangé
                    'unit'         => $item->unit?->name ?? 'unit', // kg, Tonne, etc.
                    'volume_full'  => number_format($item->volume_traded, 0, '.', ' ') . ' ' . ($item->unit?->short_name ?? ''),
                    'change'       => $trend['percentage'],
                    'status'       => $trend['status'],
                    'color'        => $trend['status'] === 'up' ? 'positive' : ($trend['status'] === 'down' ? 'negative' : 'grey'),
                    'icon'         => $trend['status'] === 'up' ? 'trending_up' : ($trend['status'] === 'down' ? 'trending_down' : 'remove'),
                ];
            });
        });
    }
}
