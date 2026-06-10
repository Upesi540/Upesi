<?php

namespace App\Filament\App\Resources\TraderUsers\RelationManagers;

use App\Filament\App\Resources\TraderUsersMerchantProfiles\TraderUsersMerchantProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TraderUsersMerchantProfileRelationManager extends RelationManager
{
    protected static string $relationship = 'merchantProfiles';

    protected static ?string $relatedResource = TraderUsersMerchantProfileResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->headerActions([

            ]);
    }
}
