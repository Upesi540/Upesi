<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        // --- COLONNE PRINCIPALE (Gauches - 2/3) ---
                        Grid::make(1)
                            ->columnSpan(2)
                            ->schema([
                                Section::make('Détails du Projet')
                                    ->icon('heroicon-o-information-circle')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Titre du projet')
                                            ->placeholder('ex: Aménagement d\'une zone de culture à Oyem')
                                            ->required()
                                            ->live(debounce: '500ms')
                                            ->afterStateUpdated(fn($state, $set) => $set('slug', Str::slug($state))),

                                        TextInput::make('slug')
                                            ->label('URL (Slug)')
                                            ->helperText('Généré automatiquement à partir du titre.')
                                            ->required()
                                            ->unique(ignoreRecord: true),

                                        RichEditor::make('description')
                                            ->json()
                                            ->fileAttachmentsDirectory('market-news')
                                            ->label('Description détaillée')
                                            ->placeholder('Décrivez les objectifs et les résultats du projet...')
                                            ->columnSpanFull(),
                                    ])->columns(2),

                                Section::make('Témoignages Clients')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                                    ->description('Ajoutez les retours d\'expérience des bénéficiaires du projet.')
                                    ->schema([
                                        Repeater::make('testimonials')
                                            ->label('Avis collectés')
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        FileUpload::make('customer_avatar')
                                                            ->label('Photo')
                                                            ->image()
                                                            ->avatar()
                                                            ->imageEditor()
                                                            ->directory('testimonials'),

                                                        Group::make([
                                                            TextInput::make('customer_name')
                                                                ->label('Nom du client')
                                                                ->required(),
                                                            TextInput::make('customer_role')
                                                                ->label('Entreprise / Fonction')
                                                                ->placeholder('ex: Coopérative Agricole'),
                                                        ]),
                                                    ]),

                                                // SYSTEME DE NOTATION (ÉTOILES)
                                                Select::make('rating')
                                                    ->label('Niveau de satisfaction')
                                                    ->options([
                                                        '5' => '⭐⭐⭐⭐⭐ (Excellent)',
                                                        '4' => '⭐⭐⭐⭐ (Très bon)',
                                                        '3' => '⭐⭐⭐ (Satisfaisant)',
                                                        '2' => '⭐⭐ (Moyen)',
                                                        '1' => '⭐ (Décevant)',
                                                    ])
                                                    ->default('5')
                                                    ->required()
                                                    ->native(false),

                                                Textarea::make('content')
                                                    ->label('Commentaire / Citation')
                                                    ->placeholder('Saisissez ici le témoignage textuel...')
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->collapsible()
                                            ->collapsed()
                                            ->itemLabel(
                                                fn(array $state): ?string => ($state['customer_name'] ?? 'Nouveau témoignage') .
                                                    ($state['rating'] ? " — " . str_repeat('⭐', intval($state['rating'])) : '')
                                            )
                                            ->addActionLabel('Ajouter un avis client'),
                                    ]),
                            ]),

                        // --- COLONNE LATERALE (Droite - 1/3) ---
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Classification')
                                    ->icon('heroicon-o-tag')
                                    ->schema([
                                        Select::make('status')
                                            ->label('Statut actuel')
                                            ->options([
                                                'planned' => 'Planifié',
                                                'ongoing' => 'En cours',
                                                'completed' => 'Terminé',
                                            ])
                                            ->default('completed')
                                            ->required()
                                            ->native(false),

                                        TextInput::make('client')
                                            ->label('Client principal'),

                                        TextInput::make('location')
                                            ->label('Lieu du projet')
                                            ->placeholder('ex: Libreville, Gabon'),
                                    ]),

                                Section::make('Visuels du Projet')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        FileUpload::make('image_path')
                                            ->label('Image de couverture')
                                            ->image()
                                            ->directory('projects/covers')
                                            ->helperText('Image principale affichée sur les listes.'),

                                        FileUpload::make('gallery')
                                            ->label('Galerie photos')
                                            ->multiple()
                                            ->reorderable()
                                            ->image()
                                            ->directory('projects/galleries')
                                            ->helperText('Ajoutez plusieurs photos de la réalisation.'),
                                    ]),

                                Section::make('Gestion de la Publication')
                                    ->icon('heroicon-o-cog-6-tooth')
                                    ->schema([
                                        DatePicker::make('start_date')
                                            ->label('Date de début'),

                                        DatePicker::make('end_date')
                                            ->label('Date de fin'),

                                        TextInput::make('sort_order')
                                            ->label('Ordre d\'affichage')
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('Publier sur la plateforme')
                                            ->helperText('Désactiver pour masquer le projet temporairement.')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
