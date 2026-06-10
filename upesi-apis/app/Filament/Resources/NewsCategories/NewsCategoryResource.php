<?php

namespace App\Filament\Resources\NewsCategories;

use App\Filament\Resources\NewsCategories\Pages\ManageNewsCategories;
use App\Models\NewsCategory;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class NewsCategoryResource extends Resource
{
    protected static ?string $model = NewsCategory::class;

    protected static string | UnitEnum | null $navigationGroup = 'Marketing';

    // Configuration de la navigation
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Catégories d\'actu';
    // protected static string|UnitEnum|null $navigationGroup = 'Marketing';
    protected static ?string $modelLabel = 'Catégorie d\'actu';
    protected static ?string $pluralModelLabel = 'Catégories d\'actualités';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations Générales')
                    ->description('Définissez l\'apparence et l\'identité de la catégorie.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom de la catégorie')
                            ->required()
                            ->maxLength(255)
                            ->live(debounce: '500ms')
                            ->afterStateUpdated(fn(string $operation, $state, Set $set) =>
                            $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->label('Slug / URL')
                            ->required()
                            ->unique(NewsCategory::class, 'slug', ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('icon')
                            ->label('Icône')
                            ->options([
                                'cloud' => 'Météo',
                                'banknotes' => 'Prix du marché',
                                'truck' => 'Logistique',
                                'megaphone' => 'Alertes',
                                'academic-cap' => 'Conseils',
                            ])
                            ->searchable(),

                        ColorPicker::make('color')
                            ->label('Couleur distinctive'),

                        TextInput::make('sort_order')
                            ->label('Ordre de tri')
                            ->numeric()
                            ->default(0)
                            ->helperText('Plus le chiffre est bas, plus la catégorie monte dans la liste.'),

                        Toggle::make('is_active')
                            ->label('Catégorie active')
                            ->default(true)
                            ->inline(false),
                    ])->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                ColorColumn::make('color')
                    ->label('Couleur'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('icon')
                    ->label('Icône')
                    ->icon(fn(string $state): string => 'heroicon-o-' . $state),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('market_news_count')
                    ->label('Articles')
                    ->counts('marketNews'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Statut d\'activation'),
            ])
            ->recordActions([
                EditAction::make(),
                // DeleteAction::make(),
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
            'index' => ManageNewsCategories::route('/'),
        ];
    }
}
