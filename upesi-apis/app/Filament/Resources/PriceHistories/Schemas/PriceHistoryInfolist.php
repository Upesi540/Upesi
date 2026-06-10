<?php

namespace App\Filament\Resources\PriceHistories\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist; // Utilise Infolist au lieu de Schema pour Filament v3
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PriceHistoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Section 1 : Informations principales
                Section::make('Détails du Produit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('crop.name')
                            ->label('Produit Agricole')
                            ->weight('bold')
                            ->color('primary'),
                        TextEntry::make('recorded_at')
                            ->label('Date du relevé')
                            ->date('d F Y'), // Affiche "13 Mars 2026"
                    ]),

                // Section 2 : Analyse des Prix
                Section::make('Analyse des Cours')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('min_price')
                            ->label('Prix Minimum')
                            ->money(config('app.base_currency'))
                            ->color('danger'),
                        TextEntry::make('average_price')
                            ->label('Prix Moyen')
                            ->money(config('app.base_currency'))
                            ->weight('bold')
                            ->color('success'),
                        TextEntry::make('max_price')
                            ->label('Prix Maximum')
                            ->money(config('app.base_currency'))
                            ->color('warning'),
                    ]),

                // Section 3 : Logistique & Stock
                Section::make('Volume & Unité')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('volume_quantity')
                            ->label('Quantité enregistrée')
                            ->numeric(),
                        TextEntry::make('unit.name')
                            ->label('Unité de mesure'),
                    ]),

                // Section Technique (plus discrète)
                Section::make('Informations Système')
                    ->collapsed() // Masqué par défaut
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('id')->label('ID Technique')->copyable(),
                            TextEntry::make('created_at')->label('Créé le')->dateTime(),
                            TextEntry::make('updated_at')->label('Dernière modif')->dateTime(),
                        ]),
                    ]),
            ]);
    }
}
