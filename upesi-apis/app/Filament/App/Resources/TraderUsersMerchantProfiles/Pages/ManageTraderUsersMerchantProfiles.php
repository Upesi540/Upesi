<?php

namespace App\Filament\App\Resources\TraderUsersMerchantProfiles\Pages;

use App\Filament\App\Resources\TraderUsersMerchantProfiles\TraderUsersMerchantProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ManageTraderUsersMerchantProfiles extends ListRecords
{
    protected static string $resource = TraderUsersMerchantProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
