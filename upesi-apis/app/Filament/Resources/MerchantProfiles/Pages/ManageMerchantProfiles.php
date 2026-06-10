<?php

namespace App\Filament\Resources\MerchantProfiles\Pages;

use App\Filament\Resources\MerchantProfiles\MerchantProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ManageMerchantProfiles extends ListRecords
{
    protected static string $resource = MerchantProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
