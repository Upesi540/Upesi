<?php

namespace App\Filament\Resources\PushSubscriptions\Pages;

use App\Filament\Resources\PushSubscriptions\PushSubscriptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePushSubscriptions extends ManageRecords
{
    protected static string $resource = PushSubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
