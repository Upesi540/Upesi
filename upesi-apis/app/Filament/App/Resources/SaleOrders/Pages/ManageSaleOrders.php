<?php

namespace App\Filament\App\Resources\SaleOrders\Pages;

use App\Filament\App\Resources\SaleOrders\SaleOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSaleOrders extends ManageRecords
{
    protected static string $resource = SaleOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
