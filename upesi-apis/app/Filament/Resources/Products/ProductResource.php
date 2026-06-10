<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Produits / Offres';

    protected static ?string $modelLabel = 'offre';

    protected static ?string $pluralModelLabel = 'offres';

    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Formulaire de création/édition avec assistant (wizard) en 5 étapes.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Wizard::make([
                    Step::make('Produit & Culture')
                        ->icon(Heroicon::OutlinedInformationCircle)
                        ->schema([
                            Section::make('Identification du produit')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            // Champ réservé à l'Admin
                                            Select::make('merchant_profile_id')
                                                ->label('Profil Vendeur (Source)')
                                                ->relationship('merchantProfile', 'shop_name') // Relation vers le profil
                                                ->getOptionLabelFromRecordUsing(
                                                    fn($record) =>
                                                    "{$record->user->first_name} {$record->user->last_name} — [" .
                                                        (\App\Models\MerchantProfile::TYPES[$record->type] ?? $record->type) .
                                                        "] — Boutique : {$record->shop_name}"
                                                )
                                                ->searchable(['shop_name', 'type'])
                                                ->preload()
                                                ->prefixIcon('heroicon-m-identification')
                                                ->required()
                                                // Si tu veux filtrer pour n'afficher que les profils approuvés par l'admin
                                                ->options(function () {
                                                    return \App\Models\MerchantProfile::query()
                                                        ->with('user')
                                                        ->where('status', 'approved')
                                                        ->get()
                                                        ->mapWithKeys(function ($profile) {
                                                            $typeName = \App\Models\MerchantProfile::TYPES[$profile->type] ?? $profile->type;
                                                            return [
                                                                $profile->id => "{$profile->user->first_name} ({$typeName}) - {$profile->shop_name}"
                                                            ];
                                                        });
                                                })
                                                ->helperText('Choisissez le profil spécifique qui sera rattaché à cette boutique/produit.'),
                                            TextInput::make('title')
                                                ->label('Titre de l\'offre')
                                                ->required()
                                                ->maxLength(255)
                                                ->placeholder('Ex: Maïs bio de qualité supérieure'),

                                            Select::make('crop_id')
                                                ->label('Culture')
                                                ->relationship('crop', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->createOptionForm([
                                                    TextInput::make('name')
                                                        ->label('Nom de la culture')
                                                        ->required(),
                                                    Select::make('category_id')
                                                        ->label('Catégorie')
                                                        ->relationship('category', 'name')
                                                        ->searchable()
                                                        ->preload()
                                                        ->required()
                                                ]),

                                            Textarea::make('description')
                                                ->label('Description détaillée')
                                                ->rows(4)
                                                ->placeholder('Décrivez votre produit, ses caractéristiques, sa qualité...')
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Prix & Quantité')
                        ->icon(Heroicon::OutlinedCurrencyDollar)
                        ->schema([
                            Section::make('Tarification et stock')
                                ->description('Définissez les prix et les quantités disponibles')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('quantity')
                                                ->label('Quantité disponible')
                                                ->numeric()
                                                ->required()
                                                ->minValue(0)
                                                ->step(0.01)
                                                ->placeholder('Ex: 1000')
                                                ->suffix(fn($record) => $record?->unit?->name ?? ''),

                                            TextInput::make('unit_price')
                                                ->label('Prix unitaire')
                                                ->numeric()
                                                ->required()
                                                ->minValue(0)
                                                ->step(0.01)
                                                ->prefix('FCFA')
                                                ->placeholder('Ex: 500'),

                                            Select::make('unit_id')
                                                ->label('Unité de vente')
                                                ->relationship('unit', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->placeholder('Sac, kg, litre...'),
                                        ]),

                                    Grid::make(2)
                                        ->schema([
                                            TextInput::make('min_order_quantity')
                                                ->label('Quantité minimum de commande')
                                                ->numeric()
                                                ->required()
                                                ->default(1)
                                                ->minValue(0.01)
                                                ->step(0.01)
                                                ->helperText('Quantité minimale que l\'acheteur peut commander'),

                                            TextInput::make('sku')
                                                ->label('SKU (Référence)')
                                                ->placeholder('Généré automatiquement si vide')
                                                ->unique(ignoreRecord: true)
                                                ->disabled(true)
                                                ->maxLength(50),
                                        ]),
                                ]),
                        ]),

                    Step::make('Localisation')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->schema([
                            Section::make('Lieu de stockage')
                                ->description('Où se trouve votre produit ?')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('country_id')
                                                ->label('Pays')
                                                ->relationship('country', 'name')
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(fn(callable $set) => $set('state_id', null)),

                                            Select::make('state_id')
                                                ->label('Province / Région')
                                                ->relationship('state', 'name', function (Builder $query, callable $get) {
                                                    if ($countryId = $get('country_id')) {
                                                        $query->where('country_id', $countryId);
                                                    }
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->reactive()
                                                ->afterStateUpdated(fn(callable $set) => $set('city_id', null)),

                                            Select::make('city_id')
                                                ->label('Ville / Localité')
                                                ->relationship('city', 'name', function (Builder $query, callable $get) {
                                                    if ($stateId = $get('state_id')) {
                                                        $query->where('state_id', $stateId);
                                                    }
                                                })
                                                ->searchable()
                                                ->preload(),

                                            TextInput::make('warehouse_name')
                                                ->label('Nom de l\'entrepôt / Magasin')
                                                ->placeholder('Ex: Entrepôt principal, Dépôt A...')
                                                ->columnSpan(1),

                                            Textarea::make('address')
                                                ->label('Adresse complète')
                                                ->rows(2)
                                                ->placeholder('Adresse physique pour le retrait ou la livraison')
                                                ->columnSpanFull(),
                                        ]),

                                    Section::make('Géolocalisation (optionnel)')
                                        ->schema([
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('latitude')
                                                        ->label('Latitude')
                                                        ->numeric()
                                                        ->step(0.000001)
                                                        ->placeholder('Ex: 5.359951'),
                                                    TextInput::make('longitude')
                                                        ->label('Longitude')
                                                        ->numeric()
                                                        ->step(0.000001)
                                                        ->placeholder('Ex: -4.008256'),
                                                ]),
                                        ])->collapsible(),
                                ]),
                        ]),

                    Step::make('Médias & Infos')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Section::make('Images')
                                        ->schema([
                                            FileUpload::make('images')
                                                ->label('Photos du produit')
                                                ->image()
                                                ->required()
                                                ->minFiles(1)
                                                ->multiple()
                                                ->maxFiles(5)
                                                ->reorderable()
                                                ->directory('products/images')
                                                ->panelLayout('grid')
                                                ->panelAspectRatio('2:1')
                                                ->helperText('Format: JPG, PNG, WebP. Max 5 images.'),
                                        ]),

                                    Section::make('Informations de récolte')
                                        ->schema([
                                            Repeater::make('harvest_info')
                                                ->label('Périodes de récolte')
                                                ->schema([
                                                    TextInput::make('season')
                                                        ->label('Saison')
                                                        ->placeholder('Ex: Printemps 2024'),
                                                    TextInput::make('date')
                                                        ->label('Date')
                                                        ->placeholder('Ex: Mars - Avril'),
                                                ])
                                                ->columns(2)
                                                ->defaultItems(0)
                                                ->collapsible(),
                                        ]),
                                ]),
                        ]),

                    Step::make('Statut & Configuration')
                        ->icon(Heroicon::OutlinedCog)
                        ->schema([
                            Section::make('Gestion de l\'offre')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('status')
                                                ->label('Statut')
                                                ->options([
                                                    'draft' => 'Brouillon',
                                                    'active' => 'Actif',
                                                    'sold' => 'Vendu',
                                                    'expired' => 'Expiré',
                                                    'inactive' => 'Inactif',
                                                ])
                                                ->default('draft')
                                                ->required()
                                                ->helperText('Une offre active est visible sur la marketplace'),

                                            Toggle::make('is_featured')
                                                ->label('Mettre en avant')
                                                ->inline(false)
                                                ->helperText('Visible uniquement par les administrateurs'),
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
     * Affichage détaillé (infolist) en français.
     */
    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Informations générales')
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Vendeur'),
                        TextEntry::make('title')->label('Titre'),
                        TextEntry::make('crop.name')->label('Culture'),
                        TextEntry::make('description')->label('Description')->markdown(),
                    ])->columns(2),

                Section::make('Prix et quantité')
                    ->schema([
                        TextEntry::make('quantity')
                            ->label('Quantité disponible')
                            ->numeric(decimalPlaces: 2),
                        TextEntry::make('unit_price')
                            ->label('Prix unitaire')
                            ->money(config('app.base_currency')),
                        TextEntry::make('unit.name')
                            ->label('Unité de vente'),
                        TextEntry::make('min_order_quantity')
                            ->label('Quantité minimum'),
                        TextEntry::make('sku')
                            ->label('SKU')
                            ->placeholder('-'),
                        Select::make('currency_id')
                            ->relationship('currency', 'code')
                            ->label('Devise')
                            ->default(function () {
                                // On récupère dynamiquement l'ID de la devise XOF
                                return \App\Models\Currency::where('code', 'XOF')->first()?->id;
                            })
                            // Désactive le champ pour empêcher le changement
                            ->disabled()
                            // Important : force l'envoi de la donnée même si le champ est disabled
                            ->dehydrated()
                            ->required()
                    ])->columns(3),

                Section::make('Localisation')
                    ->schema([
                        TextEntry::make('country.name')->label('Pays'),
                        TextEntry::make('state.name')->label('Province'),
                        TextEntry::make('city.name')->label('Ville'),
                        TextEntry::make('warehouse_name')->label('Entrepôt'),
                        TextEntry::make('address')->label('Adresse'),
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-'),
                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->formatStateUsing(fn($state) => $state ? number_format($state, 6) : '-'),
                    ])->columns(2),

                Section::make('Médias et informations')
                    ->schema([
                        ImageEntry::make('images')
                            ->label('Images')
                            ->default([]),
                        TextEntry::make('harvest_info')
                            ->label('Informations de récolte')
                            ->html(),
                    ]),

                Section::make('Statut')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'success',
                                'sold' => 'warning',
                                'expired' => 'danger',
                                'inactive' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn(string $state): string => [
                                'draft' => 'Brouillon',
                                'active' => 'Actif',
                                'sold' => 'Vendu',
                                'expired' => 'Expiré',
                                'inactive' => 'Inactif',
                            ][$state] ?? $state),
                        IconEntry::make('is_featured')
                            ->label('Mis en avant')
                            ->boolean(),
                    ])->columns(2),

                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('created_at')->label('Créé le')->dateTime(),
                        TextEntry::make('updated_at')->label('Mis à jour le')->dateTime(),
                        TextEntry::make('deleted_at')
                            ->label('Supprimé le')
                            ->dateTime()
                            ->visible(fn(Product $record): bool => $record->trashed()),
                    ])->columns(2),
            ]);
    }

    /**
     * Configuration de la table listant les produits.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                ImageColumn::make('images')
                    ->label('Image')
                    ->limit(1)
                    ->square()
                    ->imageHeight(40)
                    // Hauteur en pixels
                    ->stacked()
                    ->limitedRemainingText()
                    ->defaultImageUrl(url('/images/placeholder.png')),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('crop.name')
                    ->label('Culture')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Qté')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('unit_price')
                    ->label('Prix')
                    ->money(config('app.base_currency'))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('unit.name')
                    ->label('Unité')
                    ->toggleable(),
                TextColumn::make('city.name')
                    ->label('Localisation')
                    ->formatStateUsing(fn($record) => $record->city?->name ?? '-')
                    ->toggleable(),
                TextColumn::make('merchantProfile.user.first_name')
                    ->label('Vendeur')
                    ->weight('bold')
                    ->sortable()
                    // On permet de chercher par nom, prénom ou nom de boutique
                    ->searchable(['first_name', 'last_name', 'shop_name'])
                    ->formatStateUsing(fn($record): string => "{$record->merchantProfile->user->first_name} {$record->merchantProfile->user->last_name}")
                    ->description(function ($record): string {
                        $type = $record->merchantProfile->type;
                        $label = \App\Models\MerchantProfile::TYPES[$type] ?? $type;
                        $shop = $record->merchantProfile->shop_name;

                        return "Profil : {$label} | Boutique : {$shop}";
                    })
                    ->color('primary')
                    ->icon('heroicon-m-identification'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'sold' => 'warning',
                        'expired' => 'danger',
                        'inactive' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => [
                        'draft' => 'Brouillon',
                        'active' => 'Actif',
                        'sold' => 'Vendu',
                        'expired' => 'Expiré',
                        'inactive' => 'Inactif',
                    ][$state] ?? $state),
                IconColumn::make('is_featured')
                    ->label('Avant')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()->label('Corbeille'),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'active' => 'Actif',
                        'sold' => 'Vendu',
                        'expired' => 'Expiré',
                        'inactive' => 'Inactif',
                    ]),
                SelectFilter::make('crop_id')
                    ->label('Culture')
                    ->relationship('crop', 'name'),
                SelectFilter::make('country_id')
                    ->label('Pays')
                    ->relationship('country', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalHeading('Détails de l\'offre')
                    ->modalWidth('5xl'),
                EditAction::make()
                    ->modalHeading('Modifier l\'offre')
                    ->modalWidth('7xl'),
                DeleteAction::make()
                    ->modalHeading('Supprimer l\'offre'),
                ForceDeleteAction::make()
                    ->modalHeading('Supprimer définitivement'),
                RestoreAction::make()
                    ->modalHeading('Restaurer l\'offre'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Supprimer les offres sélectionnées'),
                    ForceDeleteBulkAction::make()
                        ->modalHeading('Supprimer définitivement les offres sélectionnées'),
                    RestoreBulkAction::make()
                        ->modalHeading('Restaurer les offres sélectionnées'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune offre')
            ->emptyStateDescription('Commencez par créer votre première offre.')
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag);
    }

    /**
     * Personnalisation de l'action de création (en haut de page).
     */
    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    /**
     * Pour la liaison des routes (soft deletes).
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
