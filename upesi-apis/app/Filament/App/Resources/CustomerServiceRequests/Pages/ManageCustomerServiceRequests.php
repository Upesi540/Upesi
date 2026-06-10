<?php

namespace App\Filament\App\Resources\CustomerServiceRequests\Pages;

use App\Filament\App\Resources\CustomerServiceRequests\CustomerServiceRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCustomerServiceRequests extends ManageRecords
{
    protected static string $resource = CustomerServiceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
