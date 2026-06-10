<?php

namespace App\Filament\Resources\KycVerifications\Pages;

use App\Filament\Resources\KycVerifications\KycVerificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageKycVerifications extends ManageRecords
{
    protected static string $resource = KycVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
