<?php

namespace App\Filament\App\Resources\MerchantDeliveries;

use App\Filament\App\Resources\MerchantDeliveries\Pages\ManageMerchantDeliveries;
use App\Filament\App\Resources\MerchantDeliveryResource\Pages;
use App\Models\Delivery;
use App\Models\MerchantProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class MerchantDeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    protected static string|UnitEnum|null $navigationGroup = 'Prestation & Logistique';

    protected static ?string $navigationLabel = 'Mes livraisons';

    protected static ?string $modelLabel = 'livraison';

    protected static ?string $pluralModelLabel = 'livraisons';

    public static function getNavigationBadge(): ?string
    {
        $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
        return static::getModel()::whereIn('transporter_profile_id', $profileIds)
            ->where('status', Delivery::STATUS_PENDING)
            ->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     $profileIds = MerchantProfile::where('user_id', Auth::id())->pluck('id');
    //     return parent::getEloquentQuery()
    //         ->whereIn('transporter_profile_id', $profileIds)
    //         ->with(['order', 'orderItem']);
    // }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Détails de la livraison')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('tracking_number')
                                    ->label('N° de suivi')
                                    ->disabled(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        Delivery::STATUS_PENDING => 'En attente',
                                        Delivery::STATUS_PICKED_UP => 'Récupéré',
                                        Delivery::STATUS_IN_TRANSIT => 'En transit',
                                        Delivery::STATUS_DELIVERED => 'Livré',
                                        Delivery::STATUS_FAILED => 'Échec',
                                    ])
                                    ->required(),
                                Forms\Components\DateTimePicker::make('estimated_pickup_at')
                                    ->label('Prise en charge estimée'),
                                Forms\Components\DateTimePicker::make('estimated_delivery_at')
                                    ->label('Livraison estimée'),
                                Forms\Components\DateTimePicker::make('picked_up_at')
                                    ->label('Récupéré le'),
                                Forms\Components\DateTimePicker::make('delivered_at')
                                    ->label('Livré le'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Commande n°')
                    ->searchable(),
                TextColumn::make('orderItem.product_name')
                    ->label('Produit'),
                TextColumn::make('pickup_address')
                    ->label('Prise en charge')
                    ->limit(30),
                TextColumn::make('delivery_address')
                    ->label('Livraison')
                    ->limit(30),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Delivery::STATUS_PENDING => 'warning',
                        Delivery::STATUS_PICKED_UP => 'info',
                        Delivery::STATUS_IN_TRANSIT => 'primary',
                        Delivery::STATUS_DELIVERED => 'success',
                        Delivery::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('estimated_delivery_at')
                    ->label('Livraison estimée')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Delivery::STATUS_PENDING => 'En attente',
                        Delivery::STATUS_PICKED_UP => 'Récupéré',
                        Delivery::STATUS_IN_TRANSIT => 'En transit',
                        Delivery::STATUS_DELIVERED => 'Livré',
                        Delivery::STATUS_FAILED => 'Échec',
                    ]),
            ])
            ->recordActions([
                Action::make('pickup')
                    ->label('Marquer récupéré')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === Delivery::STATUS_PENDING)
                    ->requiresConfirmation()
                    ->action(function (Delivery $record): void {
                        $record->update([
                            'status' => Delivery::STATUS_PICKED_UP,
                            'picked_up_at' => now(),
                        ]);
                        Notification::make()->title('Colis récupéré')->success()->send();
                    }),
                Action::make('transit')
                    ->label('En transit')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === Delivery::STATUS_PICKED_UP)
                    ->requiresConfirmation()
                    ->action(function (Delivery $record): void {
                        $record->update(['status' => Delivery::STATUS_IN_TRANSIT]);
                        Notification::make()->title('En transit')->info()->send();
                    }),
                Action::make('deliver')
                    ->label('Livrer')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn($record) => $record->status === Delivery::STATUS_IN_TRANSIT)
                    ->requiresConfirmation()
                    ->action(function (Delivery $record): void {
                        $record->update([
                            'status' => Delivery::STATUS_DELIVERED,
                            'delivered_at' => now(),
                        ]);
                        Notification::make()->title('Livraison terminée')->success()->send();
                    }),
                Action::make('fail')
                    ->label('Échec')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => !in_array($record->status, [Delivery::STATUS_DELIVERED, Delivery::STATUS_FAILED]))
                    ->requiresConfirmation()
                    ->action(function (Delivery $record): void {
                        $record->update(['status' => Delivery::STATUS_FAILED]);
                        Notification::make()->title('Échec de livraison')->danger()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMerchantDeliveries::route('/'),
        ];
    }
}
