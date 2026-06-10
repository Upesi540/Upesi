<?php

namespace App\Filament\App\Resources\ServiceOffers\Pages;

use App\Filament\App\Resources\ServiceOffers\ServiceOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ManageServiceOffers extends ManageRecords
{
    protected static string $resource = ServiceOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('7xl'),
        ];
    }

    public function getTabs(): array
    {
        $model = ServiceOfferResource::getModel();

        return [
            'all' => Tab::make('Tous')
                ->icon('heroicon-m-rectangle-stack')
                ->badge($model::whereHas('merchantProfile', fn($q) => $q->where('user_id', Auth::id()))->count()),

            'transporter' => Tab::make('Transporteurs')
                ->icon('heroicon-m-truck')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('merchantProfile', fn($q) => $q->where('type', 'transporter')))
                ->badge($model::whereHas('merchantProfile', fn($q) => $q->where('user_id', Auth::id())->where('type', 'transporter'))->count()),

            'provider' => Tab::make('Prestataires')
                ->icon('heroicon-m-wrench')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('merchantProfile', fn($q) => $q->where('type', 'provider')))
                ->badge($model::whereHas('merchantProfile', fn($q) => $q->where('user_id', Auth::id())->where('type', 'provider'))->count()),
        ];
    }
}
