<?php

namespace App\Filament\App\Resources\BulkTransactions\Pages;

use App\Filament\App\Resources\BulkTransactions\BulkTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBulkTransaction extends EditRecord
{
    protected static string $resource = BulkTransactionResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible($record->status === 'draft'),
            ForceDeleteAction::make()
                ->visible(false),
            RestoreAction::make()
                ->visible(false),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
