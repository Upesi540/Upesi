<?php

namespace App\Filament\App\Resources\PurchaseOrders;

use App\Filament\App\Resources\PurchaseOrders\Pages;
use App\Filament\App\Resources\PurchaseOrders\Pages\ManagePurchaseOrders;
use App\Http\Controllers\Api\OrderController;
use App\Models\MerchantProfile;
use App\Models\Order;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Mes achats';

    protected static ?string $modelLabel = 'commande';

    protected static ?string $pluralModelLabel = 'commandes';

    /**
     * Restreint aux commandes dont l'acheteur est l'utilisateur connecté.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('buyer_id', Auth::id())
            ->with('items.merchantProfile');
    }

    public static function getNavigationItems(): array
    {
        if (!static::canAccess()) {
            return [];
        }
        return parent::getNavigationItems();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('buyer_id', Auth::id())->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

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

    /**
     * Table des achats
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
                        'partial_cancelled' => 'warning',
                        'partial_refund' => 'warning',
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
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Paiement')
                    ->options([
                        'pending' => 'En attente',
                        'paid'    => 'Payé',
                        'failed'  => 'Échoué',
                        'refunded' => 'Remboursé',
                    ]),
            ])
            ->actions([
                // Action d'annulation sur la commande entière
                Action::make('cancel_whole_order')
                    ->label('Annuler toute la commande')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn(Order $order): bool => $order->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Motif')
                            ->required(),
                    ])
                    ->action(function (Order $order, array $data) {
                        try {
                            $controller = app(OrderController::class);
                            $request = request()->merge(['reason' => $data['reason']]);
                            $response = $controller->cancelWholeOrder($order->id, $request);

                            $responseData = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200) {
                                Notification::make()
                                    ->title('Commande annulée')
                                    ->body('Votre commande a été annulée et remboursée.')
                                    ->success()
                                    ->send();

                                // Redirection pour rafraîchir
                                redirect(request()->header('Referer'));
                            } else {
                                Notification::make()
                                    ->title('Erreur')
                                    ->body($responseData['message'] ?? 'Une erreur est survenue')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                // Action pour voir les détails
                Action::make('manage_items')
                    ->label('Gérer les produits')
                    ->icon('heroicon-o-shopping-bag')
                    ->modalHeading('Gestion des produits')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->form(function (Order $order) {
                        $items = $order->items;
                        $schema = [];

                        foreach ($items as $item) {
                            $schema[] = Section::make()
                                ->schema([
                                    Grid::make(6)
                                        ->schema([
                                            Placeholder::make('seller')
                                                ->label('Vendeur')
                                                ->content($item->merchantProfile?->shop_name ?? 'N/A'),
                                            Placeholder::make('product')
                                                ->label('Produit')
                                                ->content($item->product_name),
                                            Placeholder::make('quantity')
                                                ->label('Qté')
                                                ->content((string) $item->quantity),
                                            Placeholder::make('price')
                                                ->label('Prix')
                                                ->content(number_format($item->subtotal) . ' FCFA'),
                                            Placeholder::make('status')
                                                ->label('Statut')
                                                ->content(self::getSellerStatusLabel($item->seller_status))
                                                ->extraAttributes(['class' => 'font-bold']),
                                        ]),

                                    // Actions par item
                                    Grid::make(2)
                                        ->schema([
                                            Actions::make([
                                                Action::make("cancel_item_{$item->id}")
                                                    ->label('Annuler ce produit')
                                                    ->color('danger')
                                                    ->size('sm')
                                                    ->visible(in_array($item->seller_status, ['pending', 'confirmed']))
                                                    ->requiresConfirmation()
                                                    ->form([
                                                        Forms\Components\Textarea::make('reason')
                                                            ->label('Motif')
                                                            ->required(),
                                                    ])
                                                    ->action(function (array $data) use ($item) {
                                                        try {
                                                            $controller = app(OrderController::class);
                                                            $request = request()->merge(['reason' => $data['reason']]);
                                                            $response = $controller->cancelItem($item->id, $request);

                                                            $responseData = json_decode($response->getContent(), true);

                                                            if ($response->getStatusCode() === 200) {
                                                                Notification::make()
                                                                    ->title('Produit annulé')
                                                                    ->body('Le produit a été annulé et remboursé.')
                                                                    ->success()
                                                                    ->send();

                                                                // Redirection pour rafraîchir
                                                                redirect(request()->header('Referer'));
                                                            } else {
                                                                Notification::make()
                                                                    ->title('Erreur')
                                                                    ->body($responseData['message'] ?? 'Une erreur est survenue')
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        } catch (\Exception $e) {
                                                            Notification::make()
                                                                ->title('Erreur')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    }),

                                                Action::make("confirm_delivery_{$item->id}")
                                                    ->label('Confirmer réception')
                                                    ->color('success')
                                                    ->size('sm')
                                                    ->visible($item->seller_status === 'shipped')
                                                    ->requiresConfirmation()
                                                    ->action(function () use ($item) {
                                                        try {
                                                            $controller = app(OrderController::class);
                                                            $response = $controller->confirmDelivery($item->id);

                                                            $responseData = json_decode($response->getContent(), true);

                                                            if ($response->getStatusCode() === 200) {
                                                                Notification::make()
                                                                    ->title('Livraison confirmée')
                                                                    ->body('Merci pour votre confirmation.')
                                                                    ->success()
                                                                    ->send();

                                                                // Redirection pour rafraîchir
                                                                redirect(request()->header('Referer'));
                                                            } else {
                                                                Notification::make()
                                                                    ->title('Erreur')
                                                                    ->body($responseData['message'] ?? 'Une erreur est survenue')
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        } catch (\Exception $e) {
                                                            Notification::make()
                                                                ->title('Erreur')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    }),
                                            ])->columnSpan(2),
                                        ]),
                                ])->collapsible();
                        }

                        return $schema;
                    }),
            ])
            ->defaultSort('ordered_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePurchaseOrders::route('/'),
        ];
    }
}
