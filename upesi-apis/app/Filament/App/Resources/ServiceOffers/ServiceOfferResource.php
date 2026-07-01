<?php

namespace App\Filament\App\Resources\ServiceOffers;

use App\Filament\App\Resources\ServiceOffers\Pages\ManageServiceOffers;
use App\Models\MerchantProfile;
use App\Models\Service;
use App\Models\ServiceOffer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use App\Traits\HasProfileBasedAccess;

class ServiceOfferResource extends Resource
{
    protected static ?string $model = ServiceOffer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    protected static ?string $navigationLabel = 'Mes Offres de Services';

    protected static string|UnitEnum|null $navigationGroup = 'Prestation & Logistique';

    protected static ?string $modelLabel = 'Offre de Service';

    protected static ?string $pluralModelLabel = 'Offres de Services';
    use HasProfileBasedAccess;

    public static function canAccess(): bool
    {
        return (new static())->canAccessResource(['provider', 'transporter']);
    }

    /**
     * Restreint les requêtes au marchand connecté (via ses profils)
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('merchantProfile', function ($query) {
                $query->where('user_id', Auth::id());
            });
    }

    /**
     * Formulaire de création/édition pour le marchand
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Informations générales')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->schema([
                            Section::make('Détails du service')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            // Sélection du profil marchand (filtré automatiquement)
                                            Select::make('merchant_profile_id')
                                                ->label('Boutique')
                                                ->relationship(
                                                    name: 'merchantProfile',
                                                    titleAttribute: 'shop_name',
                                                    modifyQueryUsing: fn(Builder $query) => $query
                                                        ->where('user_id', Auth::id())
                                                        ->whereIn('type', ['transporter', 'provider'])
                                                )
                                                ->getOptionLabelFromRecordUsing(
                                                    fn($record) =>
                                                    $record->shop_name . ' (' . MerchantProfile::TYPES[$record->type] . ')'
                                                )
                                                ->required()
                                                ->native(false)
                                                ->reactive()
                                                ->default(function () {
                                                    $profiles = \App\Models\MerchantProfile::where('user_id', Auth::id())
                                                        ->whereIn('type', ['transporter', 'provider'])
                                                        ->get();
                                                    return $profiles->count() === 1 ? $profiles->first()->id : null;
                                                })
                                                ->helperText('Choisissez la boutique associée à ce service (transporteur ou prestataire).')
                                                ->prefixIcon('heroicon-m-building-storefront'),

                                            Select::make('service_id')
                                                ->label('Type de service')
                                                ->relationship(
                                                    name: 'service',
                                                    titleAttribute: 'name',
                                                    modifyQueryUsing: function (Builder $query, $get) {
                                                        $profileId = $get('merchant_profile_id');

                                                        if ($profileId) {
                                                            $profile = \App\Models\MerchantProfile::find($profileId);

                                                            if ($profile) {
                                                                if ($profile->type === 'transporter') {
                                                                    $query->whereHas('category', function ($q) {
                                                                        $q->where('slug', 'logistique');
                                                                    });
                                                                } elseif ($profile->type === 'provider') {
                                                                    $query->whereHas('category', function ($q) {
                                                                        $q->where('slug', 'prestation');
                                                                    });
                                                                }
                                                            }
                                                        }

                                                        return $query;
                                                    }
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->helperText(function ($get) {
                                                    $profileId = $get('merchant_profile_id');
                                                    if ($profileId) {
                                                        $profile = \App\Models\MerchantProfile::find($profileId);
                                                        if ($profile?->type === 'transporter') {
                                                            return 'Sélectionnez le type de transport (urbain, national, etc.)';
                                                        } elseif ($profile?->type === 'provider') {
                                                            return 'Sélectionnez le type de prestation (défrichage, labour, etc.)';
                                                        }
                                                    }
                                                    return 'Sélectionnez d\'abord une boutique';
                                                })
                                                ->prefixIcon('heroicon-m-tag'),
                                            TextInput::make('title')
                                                ->label('Titre du service')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ex: Transport de marchandises, Défrichage de terrain...'),


                                            Textarea::make('description')
                                                ->label('Description détaillée')
                                                ->rows(4)
                                                ->placeholder('Décrivez votre service, vos conditions, votre matériel...')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Tarification')
                        ->icon(Heroicon::OutlinedCurrencyDollar)
                        ->schema([
                            Section::make('Prix et conditions')
                                ->description('Définissez le prix et les modalités')
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
                        ]),

                    Step::make('Zones d\'intervention')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->schema([
                            Section::make('Localisation')
                                ->description('Où proposez-vous ce service ?')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('service_zones')
                                                ->label('Zones d\'intervention')
                                                ->options(\App\Models\State::pluck('name', 'id'))
                                                ->multiple()
                                                ->searchable()
                                                ->helperText('Sélectionnez les régions où vous proposez ce service'),

                                            TextInput::make('location_name')
                                                ->label('Point de départ / Adresse')
                                                ->placeholder('Ex: Libreville, Quartier Montagne'),

                                            // TextInput::make('latitude')
                                            //     ->label('Latitude')
                                            //     ->numeric()
                                            //     ->step(0.000001)
                                            //     ->placeholder('Ex: 0.416198'),

                                            // TextInput::make('longitude')
                                            //     ->label('Longitude')
                                            //     ->numeric()
                                            //     ->step(0.000001)
                                            //     ->placeholder('Ex: 9.467268'),
                                        ]),
                                ]),
                        ]),

                    // Step::make('Médias')
                    //     ->icon(Heroicon::OutlinedPhoto)
                    //     ->schema([
                    //         Section::make('Images')
                    //             ->schema([
                    //                 FileUpload::make('images')
                    //                     ->label('Photos du service')
                    //                     ->image()
                    //                     ->multiple()
                    //                     ->maxFiles(5)
                    //                     ->reorderable()
                    //                     ->directory('services/images')
                    //                     ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/jpg'])
                    //                     ->panelLayout('grid')
                    //                     ->panelAspectRatio('2:1')
                    //                     ->helperText('Format: JPG, PNG, WebP. Max 5 images.'),
                    //             ]),
                    //     ]),

                    Step::make('Statut')
                        ->icon(Heroicon::OutlinedCog)
                        ->schema([
                            Section::make('Publication')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('status')
                                                ->label('Statut')
                                                ->options([
                                                    'pending' => 'En attente',
                                                    'active' => 'Actif',
                                                    'inactive' => 'Inactif',
                                                ])
                                                ->default('pending')
                                                ->required()
                                                ->helperText('Une offre active est visible sur la marketplace'),

                                            Toggle::make('is_available')
                                                ->label('Disponible')
                                                ->default(true)
                                                ->inline(false)
                                                ->helperText('Désactivez si vous n\'êtes pas disponible'),

                                            Toggle::make('is_featured')
                                                ->label('Mettre en avant')
                                                ->disabled()
                                                ->inline(false)
                                                ->helperText('Demande de mise en avant (soumis à validation admin)'),
                                        ]),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString(false),
            ]);
    }

    /**
     * Affichage détaillé pour le marchand
     */
    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('title')->label('Titre'),
                        TextEntry::make('service.name')->label('Type de service'),
                        TextEntry::make('description')->label('Description')->markdown(),
                    ])->columns(2),

                Section::make('Tarification')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Prix')
                            ->money('XOF'),
                        TextEntry::make('price_unit')
                            ->label('Unité')
                            ->formatStateUsing(fn($state) => [
                                'service' => 'Par service',
                                'heure' => 'Par heure',
                                'jour' => 'Par jour',
                                'km' => 'Par kilomètre',
                                'hectare' => 'Par hectare',
                            ][$state] ?? $state),
                    ])->columns(2),

                Section::make('Zones d\'intervention')
                    ->schema([
                        TextEntry::make('service_zones')
                            ->label('Zones')
                            ->formatStateUsing(fn($state) => implode(', ', $state ?? []))
                            ->placeholder('Aucune zone sélectionnée'),
                        TextEntry::make('location_name')->label('Adresse'),
                    ])->columns(2),

                Section::make('Médias')
                    ->schema([
                        // ImageEntry::make('images')
                        //     ->label('Images')
                        //     ->default([]),
                    ]),

                Section::make('Statut')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'success',
                                'pending' => 'warning',
                                'inactive' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => [
                                'pending' => 'En attente',
                                'active' => 'Actif',
                                'inactive' => 'Inactif',
                            ][$state] ?? $state),
                        IconEntry::make('is_available')
                            ->label('Disponible')
                            ->boolean(),
                        IconEntry::make('is_featured')
                            ->label('Mise en avant')
                            ->boolean(),
                    ])->columns(3),

                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('created_at')->label('Créé le')->dateTime(),
                        TextEntry::make('updated_at')->label('Mis à jour le')->dateTime(),
                    ])->columns(2),
            ]);
    }

    /**
     * Configuration de la table pour le marchand
     */
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                // ImageColumn::make('images')
                //     ->label('Image')
                //     ->limit(1)
                //     ->square()
                //     ->imageHeight(40)
                //     ->stacked()
                //     ->limitedRemainingText()
                //     ->defaultImageUrl(url('/images/placeholder.png')),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('service.name')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Prix')
                    ->money('XOF')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('price_unit')
                    ->label('Unité')
                    ->formatStateUsing(fn($state) => [
                        'service' => 'service',
                        'heure' => 'heure',
                        'jour' => 'jour',
                        'km' => 'km',
                        'hectare' => 'ha',
                    ][$state] ?? $state),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'inactive' => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => [
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ][$state] ?? $state),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'active' => 'Actif',
                        'inactive' => 'Inactif',
                    ]),
                SelectFilter::make('service_id')
                    ->label('Type de service')
                    ->relationship('service', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Détails du service')
                    ->modalWidth('7xl'),
                EditAction::make()
                    ->modalHeading('Modifier le service')
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->modalHeading('Supprimer le service'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun service')
            ->emptyStateDescription('Commencez par créer votre premier service.')
            ->emptyStateIcon(Heroicon::OutlinedWrench);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceOffers::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('merchantProfile', function ($query) {
            $query->where('user_id', Auth::id());
        })->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
