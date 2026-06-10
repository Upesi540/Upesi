<?php

namespace App\Filament\Resources\Services;

use App\Filament\Resources\Services\Pages\ManageServices;
use App\Models\Service;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
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

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static string | UnitEnum | null $navigationGroup = 'Prestation & Logistique';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Service';
    protected static ?string $pluralModelLabel = 'Services';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // On utilise des Sections simples qui prennent toute la largeur
                Section::make('Détails du Service')
                    ->description('Informations principales identifiant le service.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom du service')
                            ->required()
                            ->live(debounce: '500ms') // Mise à jour en temps réel (attend 0.5s après la fin de la frappe)
                            ->afterStateUpdated(fn(?string $state, $set) => $set('slug', Str::slug($state ?? ''))),
                        Select::make('service_category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name') // Relation Service -> ServiceCategory
                            ->preload()
                            ->required()
                            ->searchable(),

                        TextInput::make('slug')
                            ->label('Slug / URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly()
                            ->dehydrated(),

                        Textarea::make('description')
                            ->label('Description détaillée')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2), // Deux colonnes ici, c'est large et lisible


                // On regroupe Image et Visibilité dans une section à 2 colonnes
                Section::make('Média & Visibilité')
                    ->schema([
                        // FileUpload::make('icon')
                        //     ->label('Logo du service')
                        //     ->image()
                        //     ->imageEditor() // Permet de recadrer la photo direct dans Filament
                        //     ->directory('services/logos')
                        //     ->maxSize(200), // 100 Ko

                        // FileUpload::make('image_path')
                        //     ->label('Image du service')
                        //     ->image()
                        //     ->imageEditor() // Permet de recadrer la photo direct dans Filament
                        //     ->directory('services/banners')
                        //     ->maxSize(500), // 200 Ko

                        // Sous-groupe pour les réglages de statut
                        Group::make([
                            Toggle::make('is_active')
                                ->label('Actif')
                                ->default(true),


                            // TextInput::make('sort_order')
                            //     ->label('Ordre de tri')
                            //     ->numeric()
                            //     ->default(0),
                        ]),
                    ])->columns(2),

                Section::make('Référencement (SEO)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('Titre Meta'),
                        TagsInput::make('meta_keywords')->label('Mots-clés SEO'),
                        Textarea::make('meta_description')
                            ->label('Description Meta')
                            ->columnSpanFull(),
                    ])->columns(2),
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
                // ImageColumn::make('image_path')
                //     ->label('Image')
                //     ->square(),

                TextColumn::make('name')
                    ->label('Service')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Service $record): string => $record->slug),

                TextColumn::make('category.name')
                    ->label('Catégorie de Services')
                    ->badge()
                    ->color('success')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Statut')
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
                    EditAction::make()->modalWidth('7xl'),
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServices::route('/'),
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
