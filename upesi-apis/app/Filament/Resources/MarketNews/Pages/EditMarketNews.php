<?php

namespace App\Filament\Resources\MarketNews\Pages;

use App\Filament\Resources\MarketNews\MarketNewsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketNews extends EditRecord
{
    protected static string $resource = MarketNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
