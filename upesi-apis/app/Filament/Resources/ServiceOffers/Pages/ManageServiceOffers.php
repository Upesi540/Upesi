<?php

namespace App\Filament\Resources\ServiceOffers\Pages;

use App\Filament\Resources\ServiceOffers\ServiceOfferResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageServiceOffers extends ManageRecords
{
    protected static string $resource = ServiceOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
