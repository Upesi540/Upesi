<?php

namespace App\Filament\App\Resources\TraderUsers\Pages;

use App\Filament\App\Resources\TraderUsers\TraderUsersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTraderUser extends ViewRecord
{
    protected static string $resource = TraderUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
