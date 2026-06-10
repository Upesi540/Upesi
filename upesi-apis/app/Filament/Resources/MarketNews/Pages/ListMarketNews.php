<?php

namespace App\Filament\Resources\MarketNews\Pages;

use App\Filament\Resources\MarketNews\MarketNewsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMarketNews extends ListRecords
{
    protected static string $resource = MarketNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
