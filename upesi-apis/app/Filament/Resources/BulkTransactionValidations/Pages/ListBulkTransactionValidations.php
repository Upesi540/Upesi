<?php

namespace App\Filament\Resources\BulkTransactionValidations\Pages;

use App\Filament\Resources\BulkTransactionValidations\BulkTransactionValidationResource;
use App\Models\BulkTransaction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListBulkTransactionValidations extends ListRecords
{
    protected static string $resource = BulkTransactionValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous')
                ->badge(BulkTransaction::where('status', 'pending')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query),

            'sales' => Tab::make('Ventes groupées')
                ->icon('heroicon-m-shopping-cart')
                ->badge(BulkTransaction::where('status', 'pending')->where('type', 'sale')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'sale')),

            'purchases' => Tab::make('Achats groupés')
                ->icon('heroicon-m-truck')
                ->badge(BulkTransaction::where('status', 'pending')->where('type', 'purchase')->count())
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'purchase')),
        ];
    }
}
