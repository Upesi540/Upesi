<?php

namespace App\Filament\App\Resources\BulkTransactions\Pages;

use App\Filament\App\Resources\BulkTransactions\BulkTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBulkTransaction extends CreateRecord
{
    protected static string $resource = BulkTransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        return $data;
    }
}
