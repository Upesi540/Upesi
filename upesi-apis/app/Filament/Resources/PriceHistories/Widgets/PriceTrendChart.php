<?php

namespace App\Filament\Resources\PriceHistories\Widgets;

use App\Models\PriceHistory;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // Important
use Carbon\Carbon;

class PriceTrendChart extends ChartWidget
{
    use InteractsWithPageFilters; // Activer la connexion aux filtres de page

    protected ?string $pollingInterval = null;
    protected ?string $heading = 'Analyse des cours du marché';

    protected function getFilters(): ?array
    {
        return [
            '7' => '7 derniers jours',
            '30' => '30 derniers jours',
            '90' => '90 derniers jours',
        ];
    }

    protected function getData(): array
    {
        // 1. Récupération des filtres globaux (Page)
        $pId = $this->filters['country_id']['value'] ?? null;
        $sId = $this->filters['state_id']['value'] ?? null;
        $cId = $this->filters['city_id']['value'] ?? null;
        $cropId = $this->filters['crop_id']['value'] ?? null;

        // 2. Filtre de temps (Widget)
        $activeFilter = $this->filter ?? '30';
        $dateLimite = now()->subDays((int) $activeFilter);

        // 3. Requête filtrée
        $history = PriceHistory::query()
            ->when($cropId, fn($q) => $q->where('crop_id', $cropId))
            ->when($cId, fn($q) => $q->where('city_id', $cId))
            ->when($sId && !$cId, fn($q) => $q->where('state_id', $sId))
            ->when($pId && !$sId, fn($q) => $q->where('country_id', $pId))
            ->where('recorded_at', '>=', $dateLimite)
            ->orderBy('recorded_at', 'asc')
            ->get()
            // On s'assure que recorded_at est bien un objet Carbon dans le modèle
            ->groupBy(fn($item) => Carbon::parse($item->recorded_at)->format('d/m'));

        return [
            'datasets' => [
                [
                    'label' => 'Prix Moyen (FCFA)',
                    'data' => $history->map(fn($days) => round($days->avg('average_price'), 2))->values()->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $history->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
}
