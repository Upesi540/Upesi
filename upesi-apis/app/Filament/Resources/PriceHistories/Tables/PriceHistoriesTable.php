<?php

namespace App\Filament\Resources\PriceHistories\Tables;

use Filament\Actions\ViewAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PriceHistoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('recorded_at')
                    ->label('Date du relevé')
                    ->date('d M Y')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('primary'),

                TextColumn::make('crop.name')
                    ->label('Produit Culturel')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => json_encode($record->crop->variety) ?? 'Variété standard'),

                TextColumn::make('average_price')
                    ->label('Prix Moyen (Médiane)')
                    ->money(config('app.base_currency'))
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->alignEnd(),

                TextColumn::make('min_price')
                    ->label('Prix Min')
                    ->money(config('app.base_currency'))
                    ->sortable()
                    ->color('gray')
                    ->alignEnd()
                    ,

                TextColumn::make('max_price')
                    ->label('Prix Max')
                    ->money(config('app.base_currency'))
                    ->sortable()
                    ->color('gray')
                    ->alignEnd()
                    ,

                TextColumn::make('volume_quantity')
                    ->label('Volume Total')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->alignCenter()
                    ->suffix(fn($record) => " " . ($record->unit->name ?? ''))
                    ,

                TextColumn::make('created_at')
                    ->label('Calculé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                // \Filament\Tables\Filters\SelectFilter::make('crop_id')
                //     ->label('Filtrer par culture')
                //     ->relationship('crop', 'name')
                //     ->preload()
                //     ->searchable(),

                \Filament\Tables\Filters\Filter::make('recorded_at')
                    ->schema([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Depuis le'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Jusqu\'au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('recorded_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('recorded_at', '<=', $data['until']));
                    })
                    ->label('Période')
            ])
            ->recordActions([
                ViewAction::make()->label('Détails'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateHeading('Aucun historique de prix')
            ->emptyStateDescription('Les prix seront générés automatiquement lors de la prochaine mise à jour du marché.');
    }
}
