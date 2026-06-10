<?php

namespace App\Filament\App\Resources\MerchantServiceRequests\Pages;

use App\Filament\App\Resources\MerchantServiceRequests\MerchantServiceRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ManageMerchantServiceRequests extends ManageRecords
{
    protected static string $resource = MerchantServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $model = MerchantServiceRequestResource::getModel();

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
