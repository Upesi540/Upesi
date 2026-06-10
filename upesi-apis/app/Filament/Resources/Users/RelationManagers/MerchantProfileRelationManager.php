<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\MerchantProfiles\MerchantProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class MerchantProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'merchantProfiles';

    protected static ?string $relatedResource = MerchantProfileResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->headerActions([

            ]);
    }
}
