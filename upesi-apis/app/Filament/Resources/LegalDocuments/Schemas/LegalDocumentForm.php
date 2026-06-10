<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1. Section des informations (Empilée en haut)
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->description('Détails d\'identification du document légal.')
                    ->schema([
                        Grid::make(4) // Grille interne pour les inputs
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre du document')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('slug')
                                    ->label('Slug (URL)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('version')
                                    ->label('Version')
                                    ->default('1.0')
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Activer cette version')
                                    ->onColor('success'),
                            ]),
                    ]),

                // 2. Section du contenu (Forcée en dessous et pleine largeur)
                RichEditor::make('content')
                    ->json()
                    ->label('Contenu du document')
                    ->required()
                    ->fileAttachmentsDirectory('legal-assets')
                    ->extraInputAttributes(['style' => 'min-height: 500px;']) // La bonne méthode ici !
                    ->columnSpanFull(), // L'élément occupe toute la largeur disponible
            ]);
    }
}
