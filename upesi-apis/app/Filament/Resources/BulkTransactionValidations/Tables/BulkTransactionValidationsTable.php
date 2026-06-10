<?php

namespace App\Filament\Resources\BulkTransactionValidations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BulkTransactionValidationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Réf.')
                    ->getStateUsing(fn($record) => 'BT-' . substr($record->id, 0, 8))
                    ->copyable()
                    ->searchable(),

                TextColumn::make('type_label')
                    ->label('Type')
                    ->badge()
                    ->color(fn($record) => $record->type === 'sale' ? 'success' : 'info'),

                TextColumn::make('trader.first_name')
                    ->label('Négociant')
                    ->searchable(),

                TextColumn::make('counterparty.first_name')
                    ->label(fn($record) => ($record?->type ?? 'sale') === 'sale' ? 'Client' : 'Fournisseur')
                    ->formatStateUsing(fn($record) => $record->counterparty?->first_name ?? '-'),

                TextColumn::make('total_amount')
                    ->label('Montant')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('details_count')
                    ->label('Lignes')
                    ->counts('details'),

                TextColumn::make('created_at')
                    ->label('Demandé le')
                    ->date('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'sale' => 'Ventes groupées',
                        'purchase' => 'Achats groupés',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir et valider'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn() => false),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
    }
}
