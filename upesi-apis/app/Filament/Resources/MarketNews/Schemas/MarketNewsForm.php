<?php

namespace App\Filament\Resources\MarketNews\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MarketNewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3) // Structure en 3 colonnes pour une UI moderne
                    ->columnSpanFull()
                    ->schema([

                        // COLONNE GAUCHE : Contenu principal (2/3 de l'espace)
                        Section::make('Contenu de l\'actualité')
                            ->columnSpan(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre')
                                    ->required()
                                    ->live(debounce: '500ms')
                                    ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->label('URL (Slug)')
                                    ->required()
                                    ->unique(ignoreRecord: true),

                                RichEditor::make('content') // Plus joli pour le contenu
                                    ->label('Corps de l\'article')
                                    ->json()
                                    ->fileAttachmentsDirectory('market-news')
                                    ->extraInputAttributes(['style' => 'min-height: 500px;']) // La bonne méthode ici !
                                    ->required()
                                    ->columnSpanFull(),

                                Textarea::make('excerpt')
                                    ->label('Extrait (Résumé)')
                                    ->rows(2)
                                    ->helperText('Un résumé court pour la liste des news.')
                                    ->extraInputAttributes(['style' => 'min-height: 500px;']), // La bonne méthode ici !

                            ]),

                        // COLONNE DROITE : Paramètres & Médias (1/3 de l'espace)
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Image & Catégorie')
                                    ->schema([
                                        FileUpload::make('featured_image')
                                            ->label('Image à la une')
                                            ->image()
                                            ->directory('news-images')
                                            ->imageEditor(), // Permet de recadrer pour une UI propre

                                        Select::make('news_category_id')
                                            ->label('Catégorie')
                                            ->relationship('newsCategory', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required(),
                                    ]),

                                Section::make('Paramètres de publication')
                                    ->schema([
                                        Select::make('type')
                                            ->label('Type de contenu')
                                            ->options([
                                                'flash' => 'Flash Info',
                                                'news' => 'Actualité',
                                                'article' => 'Article de fond',
                                                'alert' => 'Alerte Critique',
                                            ])
                                            ->default('news')
                                            ->native(false),

                                        Select::make('priority')
                                            ->label('Priorité')
                                            ->options([
                                                'low' => 'Basse',
                                                'normal' => 'Normale',
                                                'high' => 'Haute',
                                                'urgent' => 'Urgente',
                                            ])
                                            ->default('normal')
                                            ->native(false),

                                        DateTimePicker::make('published_at')
                                            ->label('Date de publication')
                                            ->default(now())
                                            ->required(),

                                        DateTimePicker::make('expires_at')
                                            ->label('Date d\'expiration'),

                                        Toggle::make('is_pinned')
                                            ->label('Épingler en haut')
                                            ->inline(false),

                                        Toggle::make('is_active')
                                            ->label('Visible en ligne')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
