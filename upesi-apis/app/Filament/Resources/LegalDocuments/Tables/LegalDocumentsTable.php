<?php

namespace App\Filament\Resources\LegalDocuments\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
// use Filament\Tables\Actions\BulkActionGroup;
// use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LegalDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Identifiant (Slug)')
                    ->fontFamily('mono')
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('gray'),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('updated_at')
                    ->label('Dernière mise à jour')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                // Ajout d'un filtre rapide pour ne voir que les actifs
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut actif'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     // DeleteBulkAction::make(),
                // ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
