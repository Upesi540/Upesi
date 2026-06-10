<?php

namespace App\Filament\Resources\PriceHistories\Widgets;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Services\MarketAnalysisService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class MarketOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected ?string $pollingInterval = null;
    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $service = app(MarketAnalysisService::class);

        // 1. Extraction des filtres (avec gestion du fallback si la clé 'value' n'existe pas)
        $countryId = $this->filters['country_id']['value'] ?? null;
        $stateId   = $this->filters['state_id']['value'] ?? null;
        $cityId    = $this->filters['city_id']['value'] ?? null;
        $cropId = $this->filters['crop_id']['value'] ?? null;
        // 2. Stratégie "Anti-Zéro" : On cherche la date la plus récente en base de données
        // Si tu n'as pas de données aujourd'hui, il prendra hier, etc.
        $lastDateAvailable = PriceHistory::max('recorded_at');

        // 3. Construction de la requête de base pour les prix
        $queryBase = PriceHistory::query()
            ->when($cropId, fn($q) => $q->where('crop_id', $cropId))
            ->when($cityId, fn($q) => $q->where('city_id', $cityId))
            ->when($stateId && !$cityId, fn($q) => $q->where('state_id', $stateId))
            ->when($countryId && !$stateId, fn($q) => $q->where('country_id', $countryId));

        // On clone la requête pour calculer la moyenne et le volume sur la même période
        $avgQuery = (clone $queryBase)->whereDate('recorded_at', $lastDateAvailable ?? now());
        $todayAvg = $avgQuery->avg('average_price') ?? 0;
        $totalVolume = $avgQuery->sum('volume_quantity') ?? 0;

        // 4. Tendance (Service)
        $trend = $service->getLocalizedMarketTrend($cityId, $stateId, $countryId);

        // 5. Libellé de la date pour rassurer l'utilisateur
        $dateLabel = $lastDateAvailable
            ? "Le " . \Carbon\Carbon::parse($lastDateAvailable)->format('d/m/Y')
            : "Aujourd'hui";

        return [
            // STAT 1 : L'INDICE
            Stat::make('Indice du Marché', number_format($todayAvg, 0, '.', ' ') . ' FCFA')
                ->description($trend['percentage'] . '% ' . ($trend['status'] === 'up' ? 'en hausse' : 'en baisse') . " ($dateLabel)")
                ->descriptionIcon($trend['icon'])
                ->color($trend['color'])
                ->chart([7, 10, 5, 2, 10, 15, $todayAvg > 0 ? 12 : 0]),

            // STAT 2 : LE VOLUME
            Stat::make('Volume Échangé', number_format($totalVolume, 0, '.', ' ') . ' kg/t')
                ->description('Activité constatée dans la zone')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            // STAT 3 : LES OFFRES (Sur les produits actifs, pas seulement l'historique)
            Stat::make('Offres Actives', Product::where('status', 'active')
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->when($stateId && !$cityId, fn($q) => $q->where('state_id', $stateId))
                ->when($countryId && !$stateId, fn($q) => $q->where('country_id', $countryId))
                ->count())
                ->description('Annonces Upesi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),
        ];
    }
}
