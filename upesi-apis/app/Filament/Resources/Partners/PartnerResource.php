<?php

namespace App\Filament\Resources\Partners;

use App\Filament\Resources\Partners\Pages\ManagePartners;
use App\Models\Partner;
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
use Filament\Forms\Components\Select;
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

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $navigationLabel = 'Partenaires';
    protected static ?string $modelLabel = 'Partenaire';
    protected static ?string $pluralModelLabel = 'Partenaires';
    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                ->columnSpanFull()
                    ->schema([
                        // --- COLONNE PRINCIPALE (2/3) ---
                        Section::make('Informations Générales')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('name')
                                        ->label('Nom du partenaire')
                                        ->required()
                                        ->live(debounce: '500ms')
                                        ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                                    TextInput::make('slug')
                                        ->label('Lien (Slug)')
                                        ->required()
                                        ->unique(ignoreRecord: true),
                                ]),

                                Textarea::make('short_description')
                                    ->label('Accroche / Courte description')
                                    ->placeholder('Une phrase pour résumer le partenariat...')
                                    ->rows(2),

                                Textarea::make('description')
                                    ->label('Description complète')
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    TextInput::make('website_url')
                                        ->label('Site Web')
                                        ->url()
                                        ->prefix('https://'),

                                    TextInput::make('facebook_url')
                                        ->label('Page Facebook')
                                        ->url()
                                        ->prefix('facebook.com/'),
                                ]),
                            ]),

                        // --- COLONNE LATERALE (1/3) ---
                        Grid::make(1)
                            ->columnSpan(1)
                            ->schema([
                                Section::make('Visuels')
                                    ->schema([
                                        FileUpload::make('logo_path')
                                            ->label('Logo officiel')
                                            ->image()
                                            ->directory('partners/logos')
                                            ->required(),

                                        FileUpload::make('cover_image')
                                            ->label('Image de couverture')
                                            ->image()
                                            ->directory('partners/covers'),
                                    ]),

                                Section::make('Classification & Status')
                                    ->schema([
                                        Select::make('type')
                                            ->label('Type de partenaire')
                                            ->options([
                                                'technical' => 'Technique',
                                                'financial' => 'Financier',
                                                'institutional' => 'Institutionnel',
                                                'media' => 'Média',
                                                'commercial' => 'Commercial',
                                                'other' => 'Autre',
                                            ])
                                            ->required()
                                            ->native(false),

                                        Select::make('level')
                                            ->label('Niveau')
                                            ->options([
                                                'platinum' => 'Platine',
                                                'gold' => 'Or',
                                                'silver' => 'Argent',
                                                'standard' => 'Standard',
                                            ])
                                            ->default('standard')
                                            ->required()
                                            ->native(false),

                                        TextInput::make('sort_order')
                                            ->label('Ordre de tri')
                                            ->numeric()
                                            ->default(0),

                                        Toggle::make('is_active')
                                            ->label('Partenaire Actif')
                                            ->default(true),

                                        Toggle::make('show_on_home')
                                            ->label('Afficher sur l\'accueil')
                                            ->default(false),
                                    ]),

                                Section::make('Contact Interne')
                                    ->description('Réservé à l\'administration')
                                    ->collapsed()
                                    ->schema([
                                        TextInput::make('internal_contact_name')
                                            ->label('Nom du contact'),
                                        TextInput::make('internal_contact_email')
                                            ->label('Email de contact')
                                            ->email(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'financial' => 'success',
                        'technical' => 'warning',
                        'institutional' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('level')
                    ->label('Niveau')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                IconColumn::make('show_on_home')
                    ->label('Home')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ajouté le')
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
            'index' => ManagePartners::route('/'),
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
