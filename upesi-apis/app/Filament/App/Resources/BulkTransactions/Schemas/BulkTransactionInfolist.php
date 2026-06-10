<?php

namespace App\Filament\App\Resources\BulkTransactions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkTransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('type_label')
                                    ->label('Type')
                                    ->badge(),

                                TextEntry::make('reference')
                                    ->label('Référence')
                                    ->getStateUsing(fn($record) => 'BT-' . substr($record->id, 0, 8)),

                                TextEntry::make('counterparty.first_name')
                                    ->label(fn($record) => $record->type === 'sale' ? 'Client' : 'Fournisseur'),

                                TextEntry::make('status_label')
                                    ->label('Statut')
                                    ->badge()
                                    ->color(fn($record) => $record->statusColor),
                            ]),
                    ]),

                Section::make('Détails des produits')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('merchantProfile.user.first_name')
                                            ->label(fn($record) => $record->participant_type === 'seller' ? 'Producteur' : 'Acheteur'),

                                        TextEntry::make('product_name')
                                            ->label('Produit'),

                                        TextEntry::make('quantity')
                                            ->label('Quantité')
                                            ->formatStateUsing(fn($state, $record) => $state . ' ' . ($record->unit ?? 'kg')),

                                        TextEntry::make('unit_price')
                                            ->label('Prix unitaire')
                                            ->money('XOF'),

                                        TextEntry::make('subtotal')
                                            ->label('Sous-total')
                                            ->money('XOF'),

                                        TextEntry::make('participant_gets')
                                            ->label('Net participant')
                                            ->money('XOF')
                                            ->color('success'),
                                    ]),
                            ]),
                    ]),

                Section::make('Résumé financier')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Montant total')
                                    ->money('XOF')
                                    ->size('xl')
                                    ->weight('bold'),

                                TextEntry::make('trader_commission')
                                    ->label('Commission négociant')
                                    ->money('XOF')
                                    ->color('warning'),
                            ]),
                    ]),

                Section::make('Validation')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('validated_at')
                                    ->label('Date de validation')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('validatedBy.first_name')
                                    ->label('Validé par'),

                                TextEntry::make('validation_notes')
                                    ->label('Notes')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->visible(fn($record) => $record->validated_at !== null),

                Section::make('Dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Créé le')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Modifié le')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),
            ]);
    }
}
