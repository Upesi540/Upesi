<?php

namespace App\Filament\Resources\BulkTransactionValidations\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkTransactionValidationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Validation')
                    ->schema([
                        Placeholder::make('info')
                            ->label('')
                            ->content('Cette page permet de valider ou rejeter la transaction.')
                            ->columnSpanFull(),

                        Textarea::make('validation_notes')
                            ->label('Notes de validation')
                            ->rows(3)
                            ->placeholder('Ajoutez une note interne...'),
                    ]),
            ]);
    }
}
