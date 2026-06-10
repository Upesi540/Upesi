<?php

namespace App\Filament\App\Resources\MerchantDeliveries\Pages;

use App\Filament\App\Resources\MerchantDeliveries\MerchantDeliveryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMerchantDeliveries extends ManageRecords
{
    protected static string $resource = MerchantDeliveryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
