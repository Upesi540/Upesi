<?php

namespace App\Filament\Resources\ServiceRequests;

use App\Filament\Resources\ServiceRequests\Pages\ManageServiceRequests;

use App\Models\MerchantProfile;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use BackedEnum;
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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationLabel = 'Demandes de service';

    protected static ?string $modelLabel = 'demande';

    protected static ?string $pluralModelLabel = 'demandes';
    protected static string|UnitEnum|null $navigationGroup = 'Prestation & Logistique';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('buyer_id')
                                    ->label('Client')
                                    ->options(User::pluck('email', 'id'))
                                    ->searchable()
                                    ->required(),
                                Select::make('merchant_profile_id')
                                    ->label('Prestataire / Transporteur')
                                    ->options(function () {
                                        return MerchantProfile::with('user')
                                            ->get()
                                            ->mapWithKeys(fn($p) => [
                                                $p->id => "{$p->shop_name} ({$p->user->email})"
                                            ]);
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make('service_offer_id')
                                    ->label('Offre de service')
                                    ->options(function () {
                                        return ServiceOffer::with('service')
                                            ->get()
                                            ->mapWithKeys(fn($offer) => [
                                                $offer->id => "{$offer->title} ({$offer->service->name})"
                                            ]);
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make('status')
                                    ->label('Statut')
                                    ->options([
                                        'pending'    => 'En attente',
                                        'accepted'   => 'Accepté',
                                        'in_progress' => 'En cours',
                                        'completed'  => 'Terminé',
                                        'cancelled'  => 'Annulé',
                                        'rejected'   => 'Rejeté',
                                    ])
                                    ->required(),
                                TextInput::make('quoted_price')
                                    ->label('Devis (FCFA)')
                                    ->numeric()
                                    ->prefix('FCFA'),
                                TextInput::make('final_price')
                                    ->label('Prix final (FCFA)')
                                    ->numeric()
                                    ->prefix('FCFA'),
                            ]),
                    ]),

                Section::make('Détails')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->json(false),
                        Textarea::make('details')
                            ->label('Détails spécifiques (JSON)')
                            ->rows(5)
                            ->json(),
                    ]),

                Section::make('Dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\DateTimePicker::make('scheduled_at')
                                    ->label('Date prévue'),
                                Forms\Components\DateTimePicker::make('completed_at')
                                    ->label('Date de réalisation'),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('buyer.email')->label('Client'),
                                TextEntry::make('merchantProfile.shop_name')
                                    ->label('Prestataire')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->merchantProfile->shop_name .
                                            ' (' . MerchantProfile::TYPES[$record->merchantProfile->type] . ')'
                                    ),
                                TextEntry::make('serviceOffer.title')->label('Offre'),
                                TextEntry::make('serviceOffer.service.name')->label('Type de service'),
                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'pending'    => 'warning',
                                        'accepted'   => 'info',
                                        'in_progress' => 'primary',
                                        'completed'  => 'success',
                                        'cancelled'  => 'danger',
                                        'rejected'   => 'danger',
                                        default      => 'gray',
                                    }),
                                TextEntry::make('quoted_price')->label('Devis')->money('XOF'),
                                TextEntry::make('final_price')->label('Prix final')->money('XOF'),
                            ]),
                    ]),

                Section::make('Détails')
                    ->schema([
                        TextEntry::make('description')->label('Description')->markdown(),
                        TextEntry::make('details')
                            ->label('Détails')
                            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT) : $state),
                    ]),

                Section::make('Dates')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('scheduled_at')->label('Date prévue')->dateTime(),
                                TextEntry::make('completed_at')->label('Date de réalisation')->dateTime(),
                                TextEntry::make('created_at')->label('Créé le')->dateTime(),
                                TextEntry::make('updated_at')->label('Mis à jour le')->dateTime(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('buyer.email')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('merchantProfile.shop_name')
                    ->label('Prestataire')
                    ->formatStateUsing(
                        fn($record) =>
                        $record->merchantProfile->shop_name .
                            ' (' . MerchantProfile::TYPES[$record->merchantProfile->type] . ')'
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('merchantProfile', function ($q) use ($search) {
                            $q->where('shop_name', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('serviceOffer.title')
                    ->label('Service')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'accepted'   => 'info',
                        'in_progress' => 'primary',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        'rejected'   => 'danger',
                        default      => 'gray',
                    }),
                TextColumn::make('quoted_price')
                    ->label('Devis')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('final_price')
                    ->label('Prix final')
                    ->money('XOF')
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->label('Prévue le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending'    => 'En attente',
                        'accepted'   => 'Accepté',
                        'in_progress' => 'En cours',
                        'completed'  => 'Terminé',
                        'cancelled'  => 'Annulé',
                        'rejected'   => 'Rejeté',
                    ]),
                SelectFilter::make('merchant_profile_id')
                    ->label('Prestataire')
                    ->relationship('merchantProfile', 'shop_name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Détails de la demande')
                    ->modalWidth('5xl'),
                // EditAction::make()
                //     ->modalHeading('Modifier la demande')
                //     ->modalWidth('5xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceRequests::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['buyer', 'merchantProfile', 'serviceOffer.service'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
