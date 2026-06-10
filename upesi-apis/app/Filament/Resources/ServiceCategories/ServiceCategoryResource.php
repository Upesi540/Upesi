<?php

namespace App\Filament\Resources\ServiceCategories;

use App\Filament\Resources\ServiceCategories\Pages\ManageServiceCategories;
use App\Models\ServiceCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;
    protected static string | UnitEnum | null $navigationGroup = 'Prestation & Logistique';

    // Icône adaptée pour une pile de services
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Catégories de Services';

    protected static ?string $modelLabel = 'Catégorie de Service';

    protected static ?string $pluralModelLabel = 'Catégories de Services';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->description('Définissez les détails de base de la catégorie de service.')
                    ->components([
                        Grid::make(2)
                            ->components([
                                TextInput::make('name')
                                    ->label('Nom de la catégorie')
                                    ->placeholder('Ex: Logistique, Assurance...')
                                    ->required()
                                    ->live(debounce: '500ms')
                                    ->afterStateUpdated(
                                        fn(string $operation, $state, $set) =>
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),

                                TextInput::make('slug')
                                    ->label('Slug / Identifiant URL')
                                    ->required()
                                    ->unique(ignoreRecord: true),
                            ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Brève description des services inclus...')
                            ->default(null)
                            ->columnSpanFull(),
                    ]),

                Section::make('Visuel & Organisation')
                    ->components([
                        Grid::make(3)
                            ->components([
                                FileUpload::make('icon')
                                    ->label('Icon')
                                    ->image()
                                    ->imageEditor()
                                    ->maxSize(200)
                                    ->directory('services-categories/logos'),

                                FileUpload::make('banner_path')
                                    ->label('Bannière illustrée')
                                    ->image()
                                    ->directory('service-categories')
                                    ->default(null),

                                Grid::make(1)
                                    ->columnSpan(1)
                                    ->components([
                                        TextInput::make('sort_order')
                                            ->label('Ordre de tri')
                                            ->required()
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('Catégorie Active')
                                            ->helperText('Si désactivé, cette catégorie ne sera pas visible sur la bourse.')
                                            ->default(true)
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // TextColumn::make('id')
                //     ->label('ID')
                //     ->sortable()
                //     ->searchable(),
                ImageColumn::make('icon')
                    ->label('Icône')
                    ->square(),
                TextColumn::make('name')
                    ->label('Nom')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('services_count')
                    ->label('Services')
                    ->counts('services')
                    ->badge()
                    ->color('success')
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('Statut')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Vous pourriez ajouter un filtre ici pour le statut actif
            ])
            ->recordActions([
                EditAction::make()->modalWidth('7xl')
                    ->label('Modifier'),
                DeleteAction::make()
                    ->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServiceCategories::route('/'),
        ];
    }
}
