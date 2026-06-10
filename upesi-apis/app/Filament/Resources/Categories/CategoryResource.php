<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput; // Pour les Meta Tags
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | UnitEnum | null $navigationGroup = 'Marketplace';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Catégorie';
    protected static ?string $pluralModelLabel = 'Catégories';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // On enlève la grosse Grid lg:3 qui casse tout dans la modale
                Section::make('Informations Générales')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom de la catégorie')
                            ->required()
                            ->live(debounce: '500ms') // Mise à jour en temps réel (attend 0.5s après la fin de la frappe)
                            ->afterStateUpdated(fn(?string $state, $set) => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->label('URL (Slug)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly()
                            ->dehydrated(),

                        // Select::make('parent_id')
                        //     ->label('Catégorie parente')
                        //     ->relationship('parent', 'name')
                        //     ->searchable()
                        //     ->preload(),
                        Select::make('market_id')
                            ->label('Marché')
                            ->relationship('market', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(1), // Deux colonnes ici c'est parfait pour la largeur d'une modale

                Section::make('Description & Médias')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description détaillée')
                            ->rows(3)
                            ->columnSpanFull(),

                        // On met l'image et l'icône côte à côte
                        Grid::make(2)->schema([
                            // FileUpload::make('image_path')
                            // ->label('Image de couverture')
                            // ->image()
                            // ->imageEditor() // Permet de recadrer la photo direct dans Filament
                            // ->directory('categories'),

                            Group::make()->schema([
                                TextInput::make('icon')
                                    ->label('Icône')
                                    ->placeholder('you text icon'),

                                Toggle::make('is_active')
                                    ->label('Catégorie en ligne')
                                    ->default(true)
                                    ->inline(false),
                            ]),
                        ]),
                    ]),

                Section::make('Référencement (SEO)')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')->label('Titre Meta'),
                        TagsInput::make('meta_keywords')->label('Mots-clés SEO'),
                        Textarea::make('meta_description')->label('Description Meta'),
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
                // ImageColumn::make('image_path')
                //     ->label('Image')
                //     ->circular(),
                TextColumn::make('icon')
                    ->label('Icône'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Category $record): string => $record->slug), // Affiche le slug sous le nom

                TextColumn::make('market.name')
                    ->label('Marché')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Marché non assigné')
                    ->searchable(),
                // TextColumn::make('parent.name')
                //     ->label('Parent')
                //     ->badge()
                //     ->color('gray')
                //     ->placeholder('Racine')
                //     ->searchable(),

                // TextColumn::make('children_count')
                //     ->label('Sous-cat.')
                //     ->counts('children')
                //     ->badge()
                //     ->color('info'),

                TextColumn::make('crops_count')
                    ->label('Cultures')
                    ->counts('crops')
                    ->badge()
                    ->color('success'),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->numeric()
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
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
