<?php

namespace App\Filament\Resources\Slides;

use App\Filament\Resources\Slides\Pages\ManageSlides;
use App\Models\Category;
use App\Models\Market;
use App\Models\Slide;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Photo;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'Bannières (Slides)';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visuel')
                    ->schema([
                        TextInput::make('title')
                            ->label("Titre du slide")
                            ->required(),
                        TextInput::make('sub_title')
                            ->label("Sous-titre")
                            ->default(null),
                        FileUpload::make('image_path')
                            ->label("Image du slide")
                            ->image()
                            ->imageEditor()
                            ->directory('slides')
                            ->required(),
                    ])->columns(2),

                Section::make('Navigation')
                    ->schema([
                        TextInput::make('button_text')
                            ->label("Texte du bouton")
                            ->default('En savoir plus'),

                        Select::make('link_type')
                            ->label("Type de destination")
                            ->options([
                                'market' => 'Un Marché',
                                'category' => 'Une Catégorie',
                                'external' => 'Lien Externe / Manuel',
                            ])
                            ->live(), // Pour mettre à jour link_url dynamiquement

                        // Champ dynamique pour Marché ou Catégorie
                        Select::make('link_url')
                            ->label("Sélectionner la cible")
                            ->options(fn(Get $get) => match ($get('link_type')) {
                                'market' => Market::pluck('name', 'slug'),
                                'category' => Category::pluck('name', 'slug'),
                                default => [],
                            })
                            ->searchable()
                            ->visible(fn(Get $get) => in_array($get('link_type'), ['market', 'category'])),

                        // Champ dynamique pour URL manuelle
                        TextInput::make('link_url')
                            ->label("URL de destination")
                            ->placeholder('https://...')
                            ->url()
                            ->required()
                            ->visible(fn(Get $get) => $get('link_type') === 'external'),
                    ])->columns(2),
                // ... dans ton form schema
                Section::make('Personnalisation du Bouton')
                    ->description('Laissez le texte du bouton vide pour ne pas l\'afficher')
                    ->schema([
                        TextInput::make('button_text')
                            ->label("Texte du bouton")
                            ->placeholder('Ex: Acheter maintenant'),

                        ColorPicker::make('button_color')
                            ->label("Couleur du bouton")
                            ->default('#ff9100'),

                        ColorPicker::make('button_text_color')
                            ->label("Couleur du texte")
                            ->default('#ffffff'),
                    ])->columns(3),
                Section::make('Affichage')
                    ->schema([
                        // TextInput::make('order')
                        //     ->label("Position")
                        //     ->numeric()
                        //     ->default(0)
                        //     ->disabled(), // Géré par le drag and drop dans la table
                        Toggle::make('is_active')
                            ->label("Slide actif")
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading() // Charge la page instantanément, puis les données
            ->recordTitleAttribute('title')
            // Activation du Drag and Drop pour réordonner
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Aperçu'),
                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable(),
                TextColumn::make('link_type')
                    ->label('Type de lien')
                    ->badge(),
                TextColumn::make('order')
                    ->label('Position')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSlides::route('/'),
        ];
    }
}
