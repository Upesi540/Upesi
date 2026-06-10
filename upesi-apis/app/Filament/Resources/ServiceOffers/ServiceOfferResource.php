<?php

namespace App\Filament\Resources\ServiceOffers;

use App\Filament\Resources\ServiceOffers\Pages\ManageServiceOffers;
use App\Models\MerchantProfile;
use App\Models\Service;
use App\Models\ServiceOffer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ServiceOfferResource extends Resource
{
    protected static ?string $model = ServiceOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    protected static ?string $navigationLabel = 'Offres de Services';

    protected static string|UnitEnum|null $navigationGroup = 'Prestation & Logistique';

    protected static ?string $modelLabel = 'Offre de Service';

    protected static ?string $pluralModelLabel = 'Offres de Services';

    /**
     * Requête avec soft deletes et filtres par onglets
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Tabs::make('Service')
                    ->tabs([
                        Tab::make('Informations')
                            ->icon(Heroicon::OutlinedInformationCircle)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        // Profil marchand (avec infos boutique, type et user)
                                        Select::make('merchant_profile_id')
                                            ->label('Boutique / Profil')
                                            ->options(function () {
                                                return MerchantProfile::with('user')
                                                    ->get()
                                                    ->mapWithKeys(function ($profile) {
                                                        $typeLabel = MerchantProfile::TYPES[$profile->type] ?? $profile->type;
                                                        return [
                                                            $profile->id => "{$profile->shop_name} ({$typeLabel}) - {$profile->user->first_name} {$profile->user->last_name} ({$profile->user->email})"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->required()
                                            ->disabled(fn($record) => $record !== null)
                                            ->helperText('Profil marchand associé à ce service')
                                            ->prefixIcon('heroicon-m-building-storefront'),

                                        // Type de service
                                        Select::make('service_id')
                                            ->label('Type de service')
                                            ->options(function () {
                                                return Service::with('category')
                                                    ->get()
                                                    ->mapWithKeys(function ($service) {
                                                        return [
                                                            $service->id => "{$service->name} ({$service->category->name})"
                                                        ];
                                                    });
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->helperText('Catégorie et type de service')
                                            ->prefixIcon('heroicon-m-tag'),

                                        TextInput::make('title')
                                            ->label('Titre du service')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ex: Transport de marchandises, Défrichage de terrain...'),

                                        Textarea::make('description')
                                            ->label('Description détaillée')
                                            ->rows(3)
                                            ->placeholder('Décrivez le service...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Tarification')
                            ->icon(Heroicon::OutlinedCurrencyDollar)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('price')
                                            ->label('Prix')
                                            ->numeric()
                                            ->required()
                                            ->minValue(0)
                                            ->step(0.01)
                                            ->prefix('FCFA')
                                            ->placeholder('Ex: 5000'),

                                        Select::make('price_unit')
                                            ->label('Unité')
                                            ->options([
                                                'service' => 'Par service',
                                                'heure' => 'Par heure',
                                                'jour' => 'Par jour',
                                                'km' => 'Par kilomètre',
                                                'hectare' => 'Par hectare',
                                            ])
                                            ->default('service')
                                            ->required()
                                            ->helperText('Comment est facturé ce service ?'),
                                    ]),
                            ]),

                        Tab::make('Zones d\'intervention')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Select::make('service_zones')
                                            ->label('Zones d\'intervention')
                                            ->options(\App\Models\State::pluck('name', 'id'))
                                            ->multiple()
                                            ->searchable()
                                            ->helperText('Régions où ce service est proposé'),

                                        TextInput::make('location_name')
                                            ->label('Point de départ / Adresse')
                                            ->placeholder('Ex: Libreville, Quartier Montagne'),

                                        TextInput::make('latitude')
                                            ->label('Latitude')
                                            ->numeric()
                                            ->step(0.000001),

                                        TextInput::make('longitude')
                                            ->label('Longitude')
                                            ->numeric()
                                            ->step(0.000001),
                                    ]),
                            ]),

                        Tab::make('Images')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->schema([
                                FileUpload::make('images')
                                    ->label('Photos du service')
                                    ->image()
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->reorderable()
                                    ->directory('services/images')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                                    ->panelLayout('grid')
                                    ->panelAspectRatio('2:1')
                                    ->helperText('Format: JPG, PNG, WebP. Max 5 images.'),
                            ]),

                        Tab::make('Statut & Validation')
                            ->icon(Heroicon::OutlinedCog)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut')
                                            ->options([
                                                'pending' => 'En attente',
                                                'active' => 'Actif',
                                                'inactive' => 'Inactif',
                                                'rejected' => 'Rejeté',
                                            ])
                                            ->default('pending')
                                            ->required()
                                            ->helperText('Statut de validation du service'),

                                        Toggle::make('is_available')
                                            ->label('Disponible')
                                            ->default(true)
                                            ->inline(false),

                                        Toggle::make('is_featured')
                                            ->label('Mise en avant')
                                            ->inline(false)
                                            ->helperText('Sélectionner pour mettre en avant sur la plateforme'),

                                        Toggle::make('is_verified')
                                            ->label('Vérifié')
                                            ->default(false)
                                            ->inline(false)
                                            ->helperText('Badge de confiance'),

                                        Textarea::make('admin_notes')
                                            ->label('Notes admin')
                                            ->rows(2)
                                            ->placeholder('Raison du rejet, notes internes...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->deferLoading()
            ->columns([
                // Image
                // ImageColumn::make('images')
                //     ->label('Image')
                //     ->limit(1)
                //     ->square()
                //     ->imageHeight(40)
                //     ->defaultImageUrl(url('/images/placeholder.png')),

                // // Titre
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Type de service avec catégorie
                TextColumn::make('service.name')
                    ->label('Service')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->service?->category?->name),

                // Boutique / Profil
                TextColumn::make('merchantProfile.shop_name')
                    ->label('Boutique')
                    ->searchable()
                    ->description(fn($record) => MerchantProfile::TYPES[$record->merchantProfile?->type] ?? ''),

                // Utilisateur
                TextColumn::make('merchantProfile.user.email')
                    ->label('Utilisateur')
                    ->searchable(),

                // Prix
                TextColumn::make('price')
                    ->label('Prix')
                    ->money('XOF')
                    ->sortable()
                    ->alignEnd(),

                // Unité
                TextColumn::make('price_unit')
                    ->label('Unité')
                    ->formatStateUsing(fn($state) => [
                        'service' => 'service',
                        'heure' => 'heure',
                        'jour' => 'jour',
                        'km' => 'km',
                        'hectare' => 'ha',
                    ][$state] ?? $state),

                // Statut
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'gray',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => [
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'rejected' => 'Rejeté',
                    ][$state] ?? $state),

                // Badges
                IconColumn::make('is_available')
                    ->label('Dispo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                IconColumn::make('is_featured')
                    ->label('Avant')
                    ->boolean(),

                IconColumn::make('is_verified')
                    ->label('Vérifié')
                    ->boolean(),

                // Dates
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label('Supprimé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color('danger'),
            ])
            ->filters([
                // Filtre par statut
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                        'rejected' => 'Rejeté',
                    ]),

                // Filtre par type de profil (transporteur / prestataire)
                SelectFilter::make('profile_type')
                    ->label('Type de profil')
                    ->options([
                        'transporter' => 'Transporteur',
                        'provider' => 'Prestataire',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($value = $data['value']) {
                            $query->whereHas('merchantProfile', function ($q) use ($value) {
                                $q->where('type', $value);
                            });
                        }
                    }),

                // Filtre par catégorie de service (logistique / prestation)
                SelectFilter::make('service_category')
                    ->label('Catégorie')
                    ->options([
                        'logistique' => 'Logistique',
                        'prestation' => 'Prestation',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($value = $data['value']) {
                            $query->whereHas('service.category', function ($q) use ($value) {
                                $q->where('slug', $value);
                            });
                        }
                    }),

                // Filtre par boutique
                SelectFilter::make('merchant_profile_id')
                    ->label('Boutique')
                    ->relationship('merchantProfile', 'shop_name')
                    ->searchable(),

                // Filtre soft delete
                SelectFilter::make('trashed')
                    ->label('Corbeille')
                    ->options([
                        'with' => 'Avec supprimés',
                        'only' => 'Supprimés uniquement',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($value = $data['value']) {
                            if ($value === 'only') {
                                $query->onlyTrashed();
                            } elseif ($value === 'with') {
                                $query->withTrashed();
                            }
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Détails du service')
                    ->modalWidth('7xl'),
                EditAction::make()
                    ->modalHeading('Modifier le service')
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->modalHeading('Supprimer le service')
                    ->modalWidth('md'),
                ForceDeleteAction::make()
                    ->modalHeading('Supprimer définitivement')
                    ->modalWidth('md'),
                RestoreAction::make()
                    ->modalHeading('Restaurer le service')
                    ->modalWidth('md'),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                ForceDeleteBulkAction::make(),
                RestoreBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun service')
            ->emptyStateDescription('Les services créés par les transporteurs et prestataires apparaîtront ici.')
            ->emptyStateIcon(Heroicon::OutlinedWrench);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceOffers::route('/'),
        ];
    }
}
