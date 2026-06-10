<?php

namespace App\Filament\Resources\LegalDocuments\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Détails du document')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('title')
                                ->label('Titre'),
                            TextEntry::make('slug')
                                ->label('Slug'),
                            TextEntry::make('version')
                                ->label('Version')
                                ->badge()
                                ->color('gray'),
                        ]),
                        Grid::make(2)->schema([
                            IconEntry::make('is_active')
                                ->label('État actuel')
                                ->boolean()
                                ->trueIcon('heroicon-o-check-circle')
                                ->falseIcon('heroicon-o-x-circle')
                                ->trueColor('success')
                                ->falseColor('danger'),
                            TextEntry::make('created_at')
                                ->label('Créé le')
                                ->dateTime('d/m/Y H:i'),
                        ]),
                    ]),

                Section::make('Texte du contrat')
                    ->schema([
                        TextEntry::make('content')
                            ->label('')
                            ->columnSpanFull()
                            ->html() // Pour que le navigateur lise le HTML et non le code
                            ->formatStateUsing(function ($record) {
                                // Voici ta ligne de la doc injectée ici
                                return \Filament\Forms\Components\RichEditor\RichContentRenderer::make($record->content)
                                    ->toHtml();
                            }),
                    ]),
            ]);
    }
}
