<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use App\Filament\Resources\Orders\RelationManagers\DeliveriesRelationManager;
use App\Http\Controllers\Api\OrderController;
use App\Models\MerchantProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Commandes';
    protected static ?string $pluralModelLabel = 'Commandes';
    protected static ?string $modelLabel = 'Commande';
    protected static string | UnitEnum | null $navigationGroup = 'Marketplace';

    /**
     * Traduction des statuts
     */
    protected static function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En traitement',
            'shipped' => 'Expédiée',
            'delivered' => 'Livrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'refunded' => 'Remboursée',
            'partial_cancelled' => 'Partiellement annulée',
            'partial_refund' => 'Remboursement partiel',
            'disputed' => 'En litige',
            default => $status,
        };
    }

    protected static function getSellerStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmé par le vendeur',
            'processing' => 'En préparation',
            'shipped' => 'Expédié',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => $status,
        };
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * Formulaire d'édition (admin ne peut modifier que les statuts)
     */
    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('order_number')
                                    ->label('N° commande')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('status')
                                    ->label('Statut commande')
                                    ->options([
                                        'pending'   => 'En attente',
                                        'confirmed' => 'Confirmée',
                                        'processing' => 'En traitement',
                                        'shipped'   => 'Expédiée',
                                        'delivered' => 'Livrée',
                                        'cancelled' => 'Annulée',
                                        'refunded'  => 'Remboursée',
                                        'disputed'  => 'En litige',
                                    ])
                                    ->required(),

                                Select::make('payment_status')
                                    ->label('Statut paiement')
                                    ->options([
                                        'pending'  => 'En attente',
                                        'paid'     => 'Payé',
                                        'failed'   => 'Échoué',
                                        'refunded' => 'Remboursé',
                                        'partial_refund' => 'Remboursement partiel',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Section::make('Montants (lecture seule)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Sous-total')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('tax')
                                    ->label('Taxes')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('shipping_cost')
                                    ->label('Frais de livraison')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('service_fee')
                                    ->label('Commission')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('discount')
                                    ->label('Remise')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('FCFA')
                                    ->disabled()
                                    ->dehydrated(false),
                            ]),
                    ]),

                Section::make('Adresses')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('shipping_address')
                                    ->label('Adresse de livraison')
                                    ->rows(3)
                                    ->json(),
                                Textarea::make('billing_address')
                                    ->label('Adresse de facturation')
                                    ->rows(3)
                                    ->json(),
                            ]),
                    ]),

                Section::make('Dates')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Forms\Components\DateTimePicker::make('ordered_at')
                                    ->label('Date de commande'),
                                Forms\Components\DateTimePicker::make('confirmed_at')
                                    ->label('Confirmée le'),
                                Forms\Components\DateTimePicker::make('shipped_at')
                                    ->label('Expédiée le'),
                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Livrée le'),
                                Forms\Components\DateTimePicker::make('cancelled_at')
                                    ->label('Annulée le'),
                            ]),
                    ]),
            ]);
    }

    /**
     * Vue détaillée (infolist) avec les items multi-vendeurs
     */
    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Produits commandés')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextEntry::make('merchantProfile.shop_name')
                                            ->label('Vendeur')
                                            ->formatStateUsing(
                                                fn($record) =>
                                                $record->merchantProfile?->shop_name . ' (' . ($record->merchantProfile?->type ?? 'N/A') . ')'
                                            ),
                                        TextEntry::make('product_name')->label('Produit'),
                                        TextEntry::make('quantity')->label('Qté'),
                                        TextEntry::make('unit_price')->label('Prix unitaire')->money('XOF'),
                                        TextEntry::make('subtotal')->label('Sous-total')->money('XOF'),
                                        TextEntry::make('seller_status')
                                            ->label('Statut vendeur')
                                            ->badge()
                                            ->formatStateUsing(fn(string $state): string => self::getSellerStatusLabel($state))
                                            ->color(fn(string $state): string => match ($state) {
                                                'pending'   => 'warning',
                                                'confirmed' => 'info',
                                                'shipped'   => 'info',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                                default     => 'gray',
                                            }),
                                        TextEntry::make('seller_gets')->label('Vendeur reçoit')->money('XOF'),
                                    ]),
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('order_number')->label('N° commande'),
                                TextEntry::make('buyer.first_name')->label('Acheteur'),
                                TextEntry::make('buyer.email')->label('Email acheteur'),
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => self::getStatusLabel($state))
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending'   => 'warning',
                                        'confirmed' => 'info',
                                        'processing' => 'primary',
                                        'shipped'   => 'info',
                                        'delivered' => 'success',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'refunded'  => 'secondary',
                                        'disputed'  => 'danger',
                                        default     => 'gray',
                                    }),
                                TextEntry::make('payment_status')
                                    ->label('Paiement')
                                    ->badge()
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'pending' => 'En attente',
                                        'paid' => 'Payé',
                                        'failed' => 'Échoué',
                                        'refunded' => 'Remboursé',
                                        'partial_refund' => 'Remboursement partiel',
                                        default => $state,
                                    })
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'secondary',
                                        'partial_refund' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),

                Section::make('Montants')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')->label('Sous-total')->money('XOF'),
                                TextEntry::make('tax')->label('Taxes')->money('XOF'),
                                TextEntry::make('shipping_cost')->label('Livraison')->money('XOF'),
                                TextEntry::make('service_fee')->label('Commission')->money('XOF'),
                                TextEntry::make('discount')->label('Remise')->money('XOF'),
                                TextEntry::make('total')->label('Total')->money('XOF')->weight('bold'),
                            ]),
                    ]),

                Section::make('Adresses')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('shipping_address')
                                    ->label('Adresse de livraison')
                                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', array_filter($state)) : $state),
                                TextEntry::make('billing_address')
                                    ->label('Adresse de facturation')
                                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', array_filter($state)) : $state),
                            ]),
                    ]),


                Section::make('Dates')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('ordered_at')->label('Commandé le')->dateTime(),
                                TextEntry::make('confirmed_at')->label('Confirmé le')->dateTime(),
                                TextEntry::make('shipped_at')->label('Expédié le')->dateTime(),
                                TextEntry::make('delivered_at')->label('Livré le')->dateTime(),
                                TextEntry::make('cancelled_at')->label('Annulé le')->dateTime(),
                            ]),
                    ]),
            ]);
    }

    /**
     * Table des commandes
     */
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('order_number')
                    ->label('N° commande')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('buyer.last_name')
                    ->label('Acheteur Nom')
                    ->searchable(),
                TextColumn::make('buyer.first_name')
                    ->label('Prénom')
                    ->searchable(),


                TextColumn::make('buyer.email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => self::getStatusLabel($state))
                    ->color(fn(string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped'   => 'info',
                        'delivered' => 'success',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'refunded'  => 'secondary',
                        'disputed'  => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Paiement')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'paid' => 'Payé',
                        'failed' => 'Échoué',
                        'refunded' => 'Remboursé',
                        'partial_refund' => 'Remboursement partiel',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'secondary',
                        'partial_refund' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('ordered_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending'   => 'En attente',
                        'confirmed' => 'Confirmée',
                        'processing' => 'En traitement',
                        'shipped'   => 'Expédiée',
                        'delivered' => 'Livrée',
                        'cancelled' => 'Annulée',
                        'disputed'  => 'En litige',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'En attente',
                        'paid'    => 'Payé',
                        'failed'  => 'Échoué',
                        'refunded' => 'Remboursé',
                    ]),
                TrashedFilter::make(),
            ])
            ->actions([
                // Voir détails
                ViewAction::make()
                    ->modalHeading('Détails de la commande')
                    ->modalWidth('5xl'),

                // Éditer (admin peut modifier statuts)
                // EditAction::make()
                //     ->modalHeading('Modifier la commande')
                //     ->modalWidth('5xl'),

                // Action admin pour annuler un item spécifique (en cas de litige)
                // Action::make('admin_cancel_item')
                //     ->label('Annuler un produit')
                //     ->color('danger')
                //     ->icon('heroicon-o-x-circle')
                //     ->form([
                //         Forms\Components\Select::make('item_id')
                //             ->label('Produit à annuler')
                //             ->options(function ($record) {
                //                 return $record->items
                //                     ->whereNotIn('seller_status', ['cancelled', 'delivered'])
                //                     ->mapWithKeys(fn($item) => [
                //                         $item->id => $item->product_name . ' - ' . ($item->merchantProfile?->shop_name ?? 'N/A')
                //                     ])
                //                     ->toArray();
                //             })
                //             ->required(),
                //         Forms\Components\Textarea::make('reason')
                //             ->label('Motif')
                //             ->required(),
                //     ])
                //     ->action(function (array $data, $record) {
                //         try {
                //             $controller = app(OrderController::class);
                //             $request = request()->merge(['reason' => $data['reason']]);
                //             $response = $controller->cancelItem($data['item_id'], $request);

                //             if ($response->getStatusCode() === 200) {
                //                 Notification::make()
                //                     ->title('Produit annulé')
                //                     ->body('Le produit a été annulé et remboursé.')
                //                     ->success()
                //                     ->send();
                //                 redirect(request()->header('Referer'));
                //             } else {
                //                 Notification::make()
                //                     ->title('Erreur')
                //                     ->body(json_decode($response->getContent(), true)['message'] ?? 'Erreur')
                //                     ->danger()
                //                     ->send();
                //             }
                //         } catch (\Exception $e) {
                //             Notification::make()
                //                 ->title('Erreur')
                //                 ->body($e->getMessage())
                //                 ->danger()
                //                 ->send();
                //         }
                //     }),

                // Action admin pour annuler toute la commande
                // Action::make('admin_cancel_order')
                //     ->label('Annuler toute la commande')
                //     ->color('danger')
                //     ->icon('heroicon-o-x-circle')
                //     ->visible(fn($record) => !in_array($record->status, ['cancelled', 'delivered', 'completed']))
                //     ->requiresConfirmation()
                //     ->form([
                //         Forms\Components\Textarea::make('reason')
                //             ->label('Motif')
                //             ->required(),
                //     ])
                //     ->action(function (array $data, $record) {
                //         try {
                //             $controller = app(OrderController::class);
                //             $request = request()->merge(['reason' => $data['reason']]);
                //             $response = $controller->cancelWholeOrder($record->id, $request);

                //             if ($response->getStatusCode() === 200) {
                //                 Notification::make()
                //                     ->title('Commande annulée')
                //                     ->body('La commande a été annulée et remboursée.')
                //                     ->success()
                //                     ->send();
                //                 redirect(request()->header('Referer'));
                //             } else {
                //                 Notification::make()
                //                     ->title('Erreur')
                //                     ->body(json_decode($response->getContent(), true)['message'] ?? 'Erreur')
                //                     ->danger()
                //                     ->send();
                //             }
                //         } catch (\Exception $e) {
                //             Notification::make()
                //                 ->title('Erreur')
                //                 ->body($e->getMessage())
                //                 ->danger()
                //                 ->send();
                //         }
                //     }),

                // DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('ordered_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['buyer', 'items.merchantProfile'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
