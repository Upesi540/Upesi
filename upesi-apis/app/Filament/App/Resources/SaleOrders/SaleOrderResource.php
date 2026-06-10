<?php

namespace App\Filament\App\Resources\SaleOrders;

use App\Filament\App\Resources\SaleOrders\Pages\ManageSaleOrders;
use App\Http\Controllers\Api\OrderController;
use App\Models\MerchantProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Traits\HasProfileBasedAccess;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class SaleOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string | UnitEnum | null $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Mes ventes';

    protected static ?string $modelLabel = 'vente';

    protected static ?string $pluralModelLabel = 'ventes';

    use HasProfileBasedAccess;

    public static function canAccess(): bool
    {
        return (new static())->canAccessResource(['supplier', 'trader', 'producer']);
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
            default => $status,
        };
    }

    protected static function getSellerStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'processing' => 'En préparation',
            'shipped' => 'Expédié',
            'delivered' => 'Livré',
            'cancelled' => 'Annulé',
            'refunded' => 'Remboursé',
            default => $status,
        };
    }

    /**
     * Restreint aux commandes qui contiennent des produits du vendeur connecté.
     */
    public static function getEloquentQuery(): Builder
    {
        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');

        $orderIds = OrderItem::whereIn('merchant_profile_id', $profileIds)
            ->pluck('order_id')
            ->unique();

        return parent::getEloquentQuery()
            ->whereIn('id', $orderIds)
            ->with(['buyer', 'items.merchantProfile']);
    }

    /**
     * Table des ventes
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

                // TextColumn::make('items')
                //     ->label('Vos produits')
                //     ->formatStateUsing(function ($record) {
                //         $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
                //         $myItems = $record->items->whereIn('merchant_profile_id', $profileIds);
                //         $products = $myItems->map(fn($item) => $item->product_name . ' (' . $item->quantity . ')');
                //         $count = $products->count();

                //         if ($count <= 3) {
                //             return $products->implode(', ');
                //         }

                //         $first3 = $products->take(3)->implode(', ');
                //         $remaining = $count - 3;

                //         return $first3 . ' + ' . $remaining . ' autre(s)';
                //     })
                //     ->tooltip(function ($record) {
                //         $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
                //         $myItems = $record->items->whereIn('merchant_profile_id', $profileIds);
                //         $products = $myItems->map(fn($item) => $item->product_name . ' (' . $item->quantity . ')');
                //         return $products->implode("\n");
                //     }),

                TextColumn::make('total_for_seller')
                    ->label('Total à recevoir')
                    ->money('XOF')
                    ->state(function ($record) {
                        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
                        return $record->items
                            ->whereIn('merchant_profile_id', $profileIds)
                            ->sum('seller_gets');
                    }),

                TextColumn::make('status')
                    ->label('Statut commande')
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
                        default     => 'gray',
                    })
                    ->sortable(),

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
            ])
            ->actions([
                // Action pour voir les détails avec gestion des items
                Action::make('manage_order')
                    ->label('Gérer la commande')
                    ->icon('heroicon-o-cog')
                    ->modalHeading('Gestion de la commande')
                    ->modalWidth('5xl')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->form(function (Order $order) {
                        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
                        $myItems = $order->items->whereIn('merchant_profile_id', $profileIds);

                        $schema = [];

                        foreach ($myItems as $item) {
                            $schema[] = Section::make()
                                ->schema([
                                    Grid::make(5)
                                        ->schema([
                                            Placeholder::make('product')
                                                ->label('Produit')
                                                ->content($item->product_name),
                                            Placeholder::make('quantity')
                                                ->label('Quantité')
                                                ->content((string) $item->quantity),
                                            Placeholder::make('subtotal')
                                                ->label('Montant')
                                                ->content(number_format($item->subtotal) . ' FCFA'),
                                            Placeholder::make('seller_gets')
                                                ->label('Vous recevez')
                                                ->content(number_format($item->seller_gets) . ' FCFA')
                                                ->extraAttributes(['class' => 'text-green-600 font-bold']),
                                            Placeholder::make('status')
                                                ->label('Statut')
                                                ->content(self::getSellerStatusLabel($item->seller_status))
                                                ->extraAttributes(['class' => 'font-bold']),
                                        ]),

                                    // Actions par item
                                    Actions::make([
                                        Action::make("confirm_{$item->id}")
                                            ->label('Confirmer la commande')
                                            ->color('success')
                                            ->size('sm')
                                            ->visible($item->seller_status === 'pending')
                                            ->requiresConfirmation()
                                            ->action(function () use ($item) {
                                                try {
                                                    $controller = app(OrderController::class);
                                                    $response = $controller->confirmItem($item->id);

                                                    $responseData = json_decode($response->getContent(), true);

                                                    if ($response->getStatusCode() === 200) {
                                                        Notification::make()
                                                            ->title('Commande confirmée')
                                                            ->body('La commande a été confirmée avec succès.')
                                                            ->success()
                                                            ->send();
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

                                        Action::make("ship_{$item->id}")
                                            ->label('Marquer expédié')
                                            ->color('info')
                                            ->size('sm')
                                            ->visible($item->seller_status === 'confirmed')
                                            ->requiresConfirmation()
                                            ->form([
                                                Textarea::make('tracking_number')
                                                    ->label('Numéro de suivi')
                                                    ->nullable(),
                                            ])
                                            ->action(function (array $data) use ($item) {
                                                try {
                                                    $controller = app(OrderController::class);
                                                    $request = request()->merge(['tracking_number' => $data['tracking_number'] ?? null]);
                                                    $response = $controller->markAsShipped($item->id, $request);

                                                    $responseData = json_decode($response->getContent(), true);

                                                    if ($response->getStatusCode() === 200) {
                                                        Notification::make()
                                                            ->title('Produit expédié')
                                                            ->body('Le produit a été marqué comme expédié.')
                                                            ->success()
                                                            ->send();
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

                                        Action::make("cancel_{$item->id}")
                                            ->label('Annuler')
                                            ->color('danger')
                                            ->size('sm')
                                            ->visible(in_array($item->seller_status, ['pending', 'confirmed']))
                                            ->requiresConfirmation()
                                            ->form([
                                                Textarea::make('reason')
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
                                                            ->body('Le produit a été annulé.')
                                                            ->warning()
                                                            ->send();
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
                                    ])->columnSpan(5),
                                ])->collapsible();
                        }

                        if (empty($schema)) {
                            $schema[] = Placeholder::make('empty')
                                ->content('Aucun produit trouvé pour ce vendeur');
                        }

                        return $schema;
                    }),

                ViewAction::make()
                    ->modalHeading('Détails de la commande')
                    ->modalWidth('7xl')
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        return $data;
                    }),
            ])
            ->defaultSort('ordered_at', 'desc');
    }

    /**
     * Badge de navigation : nombre de commandes en attente
     */
    public static function getNavigationBadge(): ?string
    {
        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');

        $pendingCount = OrderItem::whereIn('merchant_profile_id', $profileIds)
            ->where('seller_status', 'pending')
            ->distinct('order_id')
            ->count('order_id');

        return $pendingCount ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSaleOrders::route('/'),
        ];
    }
}
