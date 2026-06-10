<?php

namespace App\Filament\App\Resources\BulkTransactions\Pages;

use App\Filament\App\Resources\BulkTransactions\BulkTransactionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBulkTransaction extends ViewRecord
{
    protected static string $resource = BulkTransactionResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            EditAction::make()
                ->visible(in_array($record->status, ['draft', 'pending'])),
        ];
    }
}
