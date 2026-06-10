<?php

namespace App\Filament\Resources\PriceHistories\Pages;

use App\Filament\Resources\PriceHistories\PriceHistoryResource;
use App\Filament\Resources\PriceHistories\Widgets\MarketOverview;
use App\Filament\Resources\PriceHistories\Widgets\PriceTrendChart;
use App\Models\City;
use App\Models\Crop;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema; // Utilise celui-là au lieu de Form

class ListPriceHistories extends ListRecords
{
    protected static string $resource = PriceHistoryResource::class;
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [

            // CreateAction::make(),
            FilterAction::make()
                ->schema([
                    Select::make('country_id')
                        ->label('Pays')
                        ->options(\App\Models\Country::pluck('name', 'id'))
                        ->statePath('country_id.value')
                        ->preload()
                        ->searchable()
                        ->live(),

                    Select::make('state_id')
                        ->label('Région')
                        ->searchable()
                        ->options(function (Get $get) {
                            $id = $get('country_id.value');
                            return $id
                                ? \App\Models\State::where('country_id', $id)->pluck('name', 'id')
                                : [];
                        })
                        ->statePath('state_id.value')
                        ->live(),

                    Select::make('city_id')
                        ->label('Ville')
                        ->searchable()
                        ->options(function (Get $get) {
                            $id = $get('state_id.value');
                            return $id
                                ? City::where('state_id', $id)->pluck('name', 'id')
                                : [];
                        })
                        ->statePath('city_id.value'),
                    // ...
                    Select::make('crop_id')
                        ->label('Culture / Produit')
                        ->placeholder('Toutes les cultures')
                        ->searchable()
                        ->options(Crop::pluck('name', 'id')) // Récupère Maïs, Cacao, Soja...
                        ->statePath('crop_id.value') // Indispensable pour la cohérence avec le Widget
                        ->preload(), // Cha
                ]),
        ];
    }
    /**
     * C'est cette méthode qui "force" l'affichage du bloc de filtres
     */

    protected function getHeaderWidgets(): array
    {
        return [
            MarketOverview::class, // Les compteurs en premier
            PriceTrendChart::class,
        ];
    }
    public function getHeading(): string
    {
        // On récupère les filtres ou un tableau vide s'ils n'existent pas encore
        $filters = $this->filters ?? [];

        // On extrait les valeurs en vérifiant si la clé existe ET si c'est un tableau
        $pId = isset($filters['country_id']) && is_array($filters['country_id'])
            ? ($filters['country_id']['value'] ?? null) : null;

        $sId = isset($filters['state_id']) && is_array($filters['state_id'])
            ? ($filters['state_id']['value'] ?? null) : null;

        $cId = isset($filters['city_id']) && is_array($filters['city_id'])
            ? ($filters['city_id']['value'] ?? null) : null;

        $cropId = isset($filters['crop_id']) && is_array($filters['crop_id'])
            ? ($filters['crop_id']['value'] ?? null) : null;

        // 1. Récupération de la culture
        $cropName = $cropId ? \App\Models\Crop::find($cropId)?->name : 'Toutes les cultures';

        // 2. Construction de la localisation
        $locationParts = [];

        // On ajoute dans l'ordre hiérarchique : Ville, Région, Pays
        if ($cId && $city = \App\Models\City::find($cId)) {
            $locationParts[] = $city->name;
        }

        if ($sId && $state = \App\Models\State::find($sId)) {
            $locationParts[] = $state->name;
        }

        if ($pId && $country = \App\Models\Country::find($pId)) {
            $locationParts[] = $country->name;
        }

        $fullLocation = count($locationParts) > 0
            ? implode(', ', $locationParts)
            : 'Marché Global (Afrique)';

        return "📊 $cropName | $fullLocation";
    }
}
