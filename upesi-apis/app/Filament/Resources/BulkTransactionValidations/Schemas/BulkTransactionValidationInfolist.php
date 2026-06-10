<?php

namespace App\Filament\Resources\BulkTransactionValidations\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BulkTransactionValidationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('reference')
                                    ->label('Référence')
                                    ->getStateUsing(fn($record) => 'BT-' . substr($record->id, 0, 8)),
                                TextEntry::make('type_label')
                                    ->label('Type'),
                                TextEntry::make('trader.first_name')
                                    ->label('Négociant'),
                                TextEntry::make('counterparty.first_name')
                                    ->label(fn($record) => $record->type === 'sale' ? 'Client' : 'Fournisseur'),
                                TextEntry::make('total_amount')
                                    ->label('Montant total')
                                    ->money('XOF'),
                                TextEntry::make('trader_commission')
                                    ->label('Commission négociant')
                                    ->money('XOF'),
                            ]),
                    ]),

                Section::make('Détails des produits')
                    ->schema([
                        RepeatableEntry::make('details')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('merchantProfile.user.first_name')
                                            ->label('Participant'),
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
                                            ->money('XOF'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
