<?php

namespace App\Filament\App\Resources\TraderUsers\Pages;

use App\Filament\App\Resources\TraderUsers\TraderUsersResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTraderUser extends EditRecord
{
    protected static string $resource = TraderUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

}
