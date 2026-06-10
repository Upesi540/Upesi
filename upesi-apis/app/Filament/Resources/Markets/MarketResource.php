<?php

namespace App\Filament\Resources\Markets;

use App\Filament\Resources\Markets\Pages\ManageMarkets;
use App\Models\Market;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class MarketResource extends Resource
{
    protected static ?string $model = Market::class;

    // Icône spécifique pour les boutiques/marchés
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static string |UnitEnum | null $navigationGroup = 'Marketplace';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Marché';
    protected static ?string $pluralModelLabel = 'Marchés';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // On utilise des sections simples successives pour maximiser la largeur
                Section::make('Informations Générales')
                    ->description('Nom et identité du marché agricole.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du marché')
                                ->required()
                                ->live(debounce: '500ms') // Mise à jour en temps réel (attend 0.5s après la fin de la frappe)
                                ->afterStateUpdated(fn(?string $state, $set) => $set('slug', Str::slug($state ?? ''))),

                            TextInput::make('slug')
                                ->label('Slug / URL')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->readOnly()
                                ->dehydrated(),
                        ]),


                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Médias & Visibilité')
                    ->description('Identité visuelle et présence sur le site.')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('image_path')
                                ->label('Logo / Miniature')
                                ->image()
                                ->imageEditor()
                                ->maxSize(200)
                                ->directory('markets/logos'),

                            FileUpload::make('banner_path')
                                ->label('Bannière')
                                ->image()
                                ->maxSize(500) // 200 Ko
                                ->imageEditor() // Permet de recadrer la photo direct dans Filament
                                ->directory('markets/banners'),
                        ]),

                        // Paramètres de statut alignés horizontalement
                        Grid::make(3)->schema([
                            Toggle::make('is_active')
                                ->label('Marché Actif')
                                ->default(true),

                            // TextInput::make('sort_order')
                            //     ->label('Ordre de tri')
                            //     ->numeric()
                            //     ->disabled()
                            //     ->default(0),
                        ]),
                    ]),

                Section::make('Référencement (SEO)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('meta_title')->label('Titre Meta'),
                            TagsInput::make('meta_keywords')->label('Mots-clés SEO'),
                        ]),
                        Textarea::make('meta_description')
                            ->label('Description Meta')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading() // Charge la page instantanément, puis les données
            ->recordTitleAttribute('name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Logo')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Market $record): string => "/{$record->slug}"),
                TextColumn::make('categories_count')
                    ->counts('categories')
                    ->label('Catégories')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('crops_count')
                    ->counts('crops')
                    ->label('Cutures')
                    ->badge()
                    ->color('primary')
                    ->sortable(),


                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),


                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMarkets::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
