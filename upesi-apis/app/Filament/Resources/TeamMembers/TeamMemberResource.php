<?php

namespace App\Filament\Resources\TeamMembers;

use App\Filament\Resources\TeamMembers\Pages\ManageTeamMembers;
use App\Models\TeamMember;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
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
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    // Icône plus adaptée pour une équipe
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'last_name';

    protected static ?string $navigationLabel = 'Équipe';
    protected static ?string $modelLabel = 'Équipe';
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                ->columnSpanFull()
                    ->schema([
                        // --- IDENTITÉ ET BIO (2/3) ---
                        Section::make('Identité du Membre')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('first_name')
                                        ->label('Prénom')
                                        ->required(),
                                    TextInput::make('last_name')
                                        ->label('Nom')
                                        ->required(),
                                ]),

                                TextInput::make('role')
                                    ->label('Poste / Fonction')
                                    ->placeholder('ex: Expert Agronome')
                                    ->required(),

                                Textarea::make('bio')
                                    ->label('Biographie')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),

                        // --- PHOTO ET RÉSEAUX (1/3) ---
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Photo de Profil')
                                    ->schema([
                                        FileUpload::make('photo_path')
                                            ->label('Photo')
                                            ->image()
                                            ->avatar() // UI circulaire
                                            ->imageEditor()
                                            ->directory('team-members'),
                                    ]),

                                Section::make('Coordonnées & Social')
                                    ->schema([
                                        TextInput::make('email')
                                            ->label('Adresse Email')
                                            ->email(),
                                        TextInput::make('phone')
                                            ->label('Téléphone')
                                            ->tel(),
                                        TextInput::make('linkedin_url')
                                            ->label('LinkedIn')
                                            ->url()
                                            ->prefix('linkedin.com/in/'),

                                        Grid::make(2)->schema([
                                            TextInput::make('sort_order')
                                                ->label('Ordre')
                                                ->numeric()
                                                ->default(0),
                                            Toggle::make('is_active')
                                                ->label('Actif')
                                                ->inline(false)
                                                ->default(true),
                                        ]),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->circular(),

                TextColumn::make('full_name') // Nécessite un accessor ou concaténation
                    ->label('Nom Complet')
                    ->state(fn ($record) => "$record->first_name $record->last_name")
                    ->searchable(['first_name', 'last_name']),

                TextColumn::make('role')
                    ->label('Fonction')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->copyable()
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
                    ->dateTime('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->modalWidth('7xl'),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
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
            'index' => ManageTeamMembers::route('/'),
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
