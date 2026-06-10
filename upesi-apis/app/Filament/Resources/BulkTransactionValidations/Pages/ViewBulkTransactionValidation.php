<?php

namespace App\Filament\Resources\BulkTransactionValidations\Pages;

use App\Filament\Resources\BulkTransactionValidations\BulkTransactionValidationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBulkTransactionValidation extends ViewRecord
{
    protected static string $resource = BulkTransactionValidationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
