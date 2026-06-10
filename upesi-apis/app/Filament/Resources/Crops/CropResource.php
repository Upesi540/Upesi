<?php

namespace App\Filament\Resources\Crops;

use App\Filament\Resources\Crops\Pages\ManageCrops;
use App\Filament\Resources\PriceHistories\PriceHistoryResource;
use App\Models\Crop;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CropResource extends Resource
{
    protected static ?string $model = Crop::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Cultures / Commodités';

    protected static ?string $modelLabel = 'culture';

    protected static ?string $pluralModelLabel = 'cultures';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations générales')
                    ->description('Les informations de base de la culture')
                    ->icon(Heroicon::OutlinedInformationCircle)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Ex: Maïs, Riz, Tomate...'),

                                TagsInput::make('variety')
                                    ->label('Variétés')
                                    ->placeholder('Ajouter une variété')
                                    ->helperText('Saisissez les variétés disponibles (appuyez sur Entrée pour ajouter)')
                                    ->splitKeys(['Tab', ' ', 'Enter'])
                                    ->reorderable(),

                                TagsInput::make('grade')
                                    ->label('Grades / Qualités')
                                    ->placeholder('Ajouter un grade')
                                    ->helperText('Ex: Premium, Standard, Bio...')
                                    ->splitKeys(['Tab', ' ', 'Enter'])
                                    ->reorderable(),

                                TextInput::make('scientific_name')
                                    ->label('Nom scientifique')
                                    ->maxLength(255)
                                    ->placeholder('Ex: Zea mays, Oryza sativa...'),

                                Select::make('category_id')
                                    ->label('Catégorie')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->label('Nom de la catégorie')
                                            ->required(),
                                    ]),

                                Select::make('default_unit_id')
                                    ->label('Unité par défaut')
                                    ->relationship('defaultUnit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Sélectionnez une unité'),
                            ]),
                    ]),

                Section::make('Description et culture')
                    ->description('Informations détaillées sur la culture')
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->placeholder('Description détaillée de la culture...')
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('growing_days')
                                    ->label('Durée de croissance (jours)')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(999)
                                    ->placeholder('Ex: 90'),

                                TagsInput::make('growing_seasons')
                                    ->label('Saisons de culture')
                                    ->placeholder('Ajouter une saison')
                                    ->helperText('Ex: Printemps, Été, Automne...')
                                    ->splitKeys(['Tab', ' ', 'Enter'])
                                    ->reorderable(),
                            ]),
                    ]),

                Section::make('Médias et standards')
                    ->description('Image et normes de qualité')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // FileUpload::make('image')
                                //     ->label('Image')
                                //     ->image()
                                //     ->directory('crops/images')
                                //     ->maxSize(512)
                                //     ->imageEditor()
                                //     ->imageEditorAspectRatios([
                                //         '16:9',
                                //         '4:3',
                                //         '1:1',
                                //     ])
                                //     ->helperText('Format accepté : JPG, PNG, WebP. Max 512KB'),

                                TagsInput::make('quality_standards')
                                    ->label('Normes de qualité')
                                    ->placeholder('Ajouter une norme')
                                    ->helperText('Ex: Certification bio, Label rouge, GlobalG.A.P...')
                                    ->splitKeys(['Tab', 'Enter'])
                                    ->reorderable()
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Attributs et configuration')
                    ->description('Attributs supplémentaires et activation')
                    ->icon(Heroicon::OutlinedCog)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TagsInput::make('attributes')
                                    ->label('Attributs clé-valeur')
                                    ->placeholder('clé:valeur')
                                    ->helperText('Format: couleur:jaune, type:céréale (appuyez sur Entrée pour ajouter)')
                                    ->splitKeys(['Tab', 'Enter'])
                                    ->reorderable()
                                    ->columnSpanFull(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->inline(false)
                                    ->default(true)
                                    ->helperText('Désactiver pour masquer cette culture'),
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
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('variety')
                            ->label('Variétés')
                            ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->placeholder('Non renseigné'),
                        TextEntry::make('grade')
                            ->label('Grades / Qualités')
                            ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->placeholder('Non renseigné'),
                        TextEntry::make('scientific_name')
                            ->label('Nom scientifique')
                            ->placeholder('Non renseigné'),
                        TextEntry::make('category.name')
                            ->label('Catégorie'),
                        TextEntry::make('defaultUnit.name')
                            ->label('Unité par défaut')
                            ->placeholder('Non définie'),
                    ])->columns(3),

                Section::make('Culture')
                    ->schema([
                        TextEntry::make('growing_days')
                            ->label('Durée de croissance')
                            ->formatStateUsing(fn($state) => $state ? $state . ' jours' : 'Non renseigné'),
                        TextEntry::make('growing_seasons')
                            ->label('Saisons de culture')
                            ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->placeholder('Non renseigné'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->markdown()
                            ->placeholder('Aucune description'),
                    ])->columns(2),

                Section::make('Image et standards')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Image')
                            ->defaultImageUrl(url('/images/placeholder-crop.png'))
                            ->width(200)
                            ->height(200),
                        TextEntry::make('quality_standards')
                            ->label('Normes de qualité')
                            ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state)
                            ->placeholder('Non renseigné'),
                    ])->columns(2),

                Section::make('Attributs et statut')
                    ->schema([
                        TextEntry::make('attributes')
                            ->label('Attributs')
                            ->formatStateUsing(function ($state) {
                                if (is_array($state)) {
                                    $formatted = [];
                                    foreach ($state as $item) {
                                        if (str_contains($item, ':')) {
                                            [$key, $value] = explode(':', $item, 2);
                                            $formatted[] = "<span class='font-medium'>{$key}:</span> {$value}";
                                        } else {
                                            $formatted[] = $item;
                                        }
                                    }
                                    return implode('<br>', $formatted);
                                }
                                return $state ?: 'Aucun attribut';
                            })
                            ->html()
                            ->placeholder('Aucun attribut'),
                        IconEntry::make('is_active')
                            ->label('Statut')
                            ->boolean()
                            ->trueIcon(Heroicon::OutlinedCheckCircle)
                            ->falseIcon(Heroicon::OutlinedXCircle)
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ])->columns(2),

                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Mis à jour le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                // ImageColumn::make('image')
                //     ->label('Image')
                //     ->square()
                //     ->defaultImageUrl(url('/images/placeholder-crop.webp'))
                    // ->size(50),
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                // TextColumn::make('variety')
                //     ->label('Variétés')
                //     ->searchable()
                //     ->toggleable()
                //     ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', array_slice($state, 0, 2)) . (count($state) > 2 ? '...' : '') : $state)
                //     ->placeholder('-'),

                TextColumn::make('grade')
                    ->label('Grades')
                    ->searchable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', array_slice($state, 0, 2)) . (count($state) > 2 ? '...' : '') : $state)
                    ->placeholder('-')
                    ->badge()
                    ->color('success'),
                TextColumn::make('market.name')
                    ->label('Marché')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('defaultUnit.name')
                    ->label('Unité')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('-'),
                TextColumn::make('scientific_name')
                    ->label('Nom scientifique')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),
                TextColumn::make('growing_days')
                    ->label('Jours')
                    ->numeric()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn($state) => $state ? $state . ' j' : '-'),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger'),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Mis à jour')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('default_unit_id')
                    ->label('Unité par défaut')
                    ->relationship('defaultUnit', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->placeholder('Tous')
                    ->trueLabel('Actifs uniquement')
                    ->falseLabel('Inactifs uniquement'),
            ])
            ->recordActions([
                // Dans CropResource.php (Table actions)
                Action::make('view_history')
                    ->label('Historique de prix')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->url(fn(Crop $record): string => PriceHistoryResource::getUrl('index', [
                        'tableFilters[crop_id][value]' => $record->id,
                    ])),
                ViewAction::make()
                    ->modalHeading('Détails de la culture')
                    ->modalWidth('5xl'),
                EditAction::make()
                    ->modalHeading('Modifier la culture')
                    ->modalWidth('7xl'),
                // DeleteAction::make()
                //     ->modalHeading('Supprimer la culture'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Supprimer les cultures sélectionnées'),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('Aucune culture')
            ->emptyStateDescription('Commencez par créer votre première culture.')
            ->emptyStateIcon(Heroicon::OutlinedSparkles);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCrops::route('/'),
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
}
