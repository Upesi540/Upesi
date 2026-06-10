<?php

namespace App\Filament\App\Resources\Profiles;

use App\Filament\App\Resources\Profiles\Pages\ManageProfiles;
use App\Models\Category;
use App\Models\Crop;
use App\Models\Market;
use App\Models\MerchantProfile;
use App\Models\Service;
use App\Models\State;
use App\Models\TransportType;
use App\Models\VehicleType;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms; // IMPORTANT
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class ProfileResource extends Resource
{
    use InteractsWithForms;
    protected static ?string $model = MerchantProfile::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static ?string $navigationLabel = 'Mes profils marchands';
    protected static ?string $modelLabel = 'Mes profils marchands';
    protected static ?string $pluralModelLabel = 'Mes profils marchands';
    protected static string|UnitEnum|null $navigationGroup = 'Compte & conformité kyc';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('user_id', Auth::user()->id);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([

                Tabs::make('Profile Tabs')
                    ->tabs([
                        // --- ONGLET 1 : IDENTITÉ ---
                        Tabs\Tab::make('Ma Boutique')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Informations de Base')
                                    ->description('Identifiez votre activité sur la plateforme.')
                                    ->aside() // Design moderne : texte à gauche, champs à droite
                                    ->schema([
                                        Hidden::make('user_id')
                                            ->default(Auth::user()->id),

                                        TextInput::make('shop_name')
                                            ->label('Nom de la boutique')
                                            ->placeholder('Ex: Upesi Market Export')
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-shopping-bag'),

                                        Grid::make(1)->schema([
                                            Select::make('type')
                                                ->label('Type d\'activité')
                                                ->options(MerchantProfile::TYPES)
                                                ->required()
                                                ->live()
                                                ->native(false)
                                                ->disabled(fn($record) => $record !== null) // ← Désactivé en édition
                                                ->unique(
                                                    table: 'merchant_profiles',
                                                    column: 'type',
                                                    ignorable: fn($record) => $record,
                                                    modifyRuleUsing: function ($rule, $get) {
                                                        return $rule->where('user_id', Auth::user()->id);
                                                    }
                                                )
                                                ->validationMessages([
                                                    'unique' => 'Vous avez déjà créé un profil pour cette catégorie (Producteur, Transporteur, etc.).',
                                                ])
                                                ->prefixIcon('heroicon-m-identification'),
                                            PhoneInput::make('phone')
                                                ->label('Téléphone')
                                                ->prefixIcon('heroicon-m-phone')
                                        ]),

                                        Textarea::make('description')
                                            ->label('Description')
                                            ->rows(3)
                                            ->placeholder('Parlez de vos produits...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // --- ONGLET 2 : DÉTAILS TECHNIQUES ---
                        Tabs\Tab::make('Détails Professionnels')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->visible(fn($get) => filled($get('type')))
                            ->schema([
                                Section::make('Spécifications Métier')
                                    ->description('Ces champs sont requis pour valider votre profil.')
                                    ->aside()
                                    ->schema([

                                        // BLOC PRODUCTEUR
                                        Grid::make(2)
                                            ->visible(fn($get) => $get('type') === 'producer')
                                            ->schema([
                                                TextInput::make('metadata.address')
                                                    ->label('Adresse de l\'exploitation')
                                                    ->required()
                                                    ->prefixIcon('heroicon-m-map-pin'),

                                                Select::make('metadata.location')
                                                    ->label('Région')
                                                    ->options(State::pluck('name', 'id'))
                                                    ->searchable()
                                                    ->required() // Correction validation JSON
                                                    ->native(false),

                                                TextInput::make('metadata.surface')
                                                    ->label('Superficie')
                                                    ->numeric()
                                                    ->required()
                                                    ->suffix('ha')
                                                    ->prefixIcon('heroicon-m-square-3-stack-3d'),

                                                Select::make('metadata.crops')
                                                    ->label('Spéculations (max 3)')
                                                    ->options(Crop::pluck('name', 'id'))
                                                    ->multiple()
                                                    ->maxItems(3)
                                                    ->searchable()
                                                    ->required() // Correction validation multiple
                                                // ->prefixIcon('heroicon--leaf'),
                                            ]),

                                        // BLOC NEGOCIANT
                                        Grid::make(2)
                                            ->visible(fn($get) => $get('type') === 'trader')
                                            ->schema([
                                                TextInput::make('metadata.address')->label('Adresse commerciale')->required(),
                                                Select::make('metadata.location')->label('Région')->options(State::pluck('name', 'id'))->searchable()->required(),
                                                Select::make('metadata.crops')
                                                    ->label('Produits tradés')
                                                    ->options(Crop::pluck('name', 'id'))
                                                    ->multiple()
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ]),

                                        // BLOC TRANSPORTEUR
                                        Grid::make(2)
                                            ->visible(fn($get) => $get('type') === 'transporter')
                                            ->schema([
                                                TextInput::make('metadata.address')->label('Siège social')->required()->columnSpanFull(),
                                                Select::make('metadata.location')->searchable()->label('Région de base')->options(State::pluck('name', 'id'))->required(),
                                                Select::make('metadata.transport_types')
                                                    ->label('Types de transport')
                                                    ->options(
                                                        Service::whereHas('category', function ($q) {
                                                            $q->where('slug', 'logistique'); // ou where('name', 'Transport')
                                                        })->pluck('name', 'id')
                                                    )
                                                    ->multiple()
                                                    ->required(),
                                                Select::make('metadata.vehicle_type')->label('Moyens de transport')->options(VehicleType::pluck('name', 'id'))->multiple()->required()->columnSpanFull(),
                                            ]),

                                        // BLOC PRESTATAIRE
                                        Grid::make(2)
                                            ->visible(fn($get) => $get('type') === 'provider')
                                            ->schema([
                                                TextInput::make('metadata.personal_address')->label('Adresse personnelle')->required(),
                                                TextInput::make('metadata.company_address')->label('Adresse entreprise')->required(),
                                                Select::make('metadata.service_zone')->searchable()->label('Zones d\'intervention')->options(State::pluck('name', 'id'))->multiple()->required(),
                                                Select::make('metadata.service_type')->searchable()->label('Types de services')->options(Service::pluck('name', 'id'))->multiple()->required()->live(),
                                                TextInput::make('metadata.other_service')
                                                    ->label('Précisez le service')
                                                    ->required()
                                                    ->visible(fn($get) => in_array('other', $get('metadata.service_type') ?? []))
                                                    ->columnSpanFull(),
                                            ]),

                                        // BLOC FOURNISSEUR
                                        Grid::make(2)
                                            ->visible(fn($get) => $get('type') === 'supplier')
                                            ->schema([
                                                TextInput::make('metadata.personal_address')->label('Adresse personnelle')->required(),
                                                TextInput::make('metadata.company_address')->label('Adresse société')->required(),
                                                Select::make('metadata.location')->searchable()->label('Localisation principale(Région)')->options(State::pluck('name', 'id'))->required(),
                                                Select::make('metadata.categories')->searchable()->label('Catégories produits')->options(Category::pluck('name', 'id'))->multiple()->required(),
                                            ]),
                                    ]),
                            ]),

                        // --- ONGLET 3 : STATUS ---
                        Tabs\Tab::make('Vérification')
                            ->icon('heroicon-m-check-badge')
                            ->schema([
                                Section::make('Statut du profil')
                                    ->aside()
                                    ->schema([
                                        Placeholder::make('status_display')
                                            ->label('État actuel')
                                            ->content(fn($record) => match ($record?->status) {
                                                'approved' => '✅ Profil Approuvé',
                                                'rejected' => '❌ Profil Rejeté',
                                                default => '⏳ En attente de validation',
                                            }),

                                        Placeholder::make('completion')
                                            ->label('Complétion du profil')
                                            ->content(fn($record) => self::calculateCompletion($record) . '%'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(), // Garde l'onglet actif après sauvegarde
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('shop_name')
                    ->label('Boutique')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => "Type: " . (MerchantProfile::TYPES[$record->type] ?? $record->type)),

                TextColumn::make('phone')
                    ->label('Contact')
                    ->icon('heroicon-m-phone')
                    ->copyable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'approved' => 'Approuvé',
                        'rejected' => 'Rejeté',
                        default => 'En attente',
                    }),

                TextColumn::make('completion')
                    ->label('Complétion')
                    ->state(fn($record) => self::calculateCompletion($record))
                    ->numeric()
                    ->alignCenter()
                    ->color(fn($state) => (int) $state < 100 ? 'warning' : 'success')
                    ->suffix('%'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->color('primary'),
                ]),
            ])
            ->toolbarActions([]);
    }

    protected static function calculateCompletion($record): int
    {
        if (!$record) return 0;

        $score = 0;
        $total = 3;

        if ($record->shop_name) $score++;
        if ($record->phone) $score++;
        if ($record->description) $score++;

        $metadata = $record->metadata ?? [];
        $typeRules = [
            'producer' => ['address', 'location', 'surface', 'crops'],
            'trader' => ['address', 'location', 'crops'],
            'transporter' => ['address', 'location', 'transport_types', 'vehicle_type'],
            'provider' => ['personal_address', 'company_address', 'service_zone', 'service_type'],
            'supplier' => ['personal_address', 'company_address', 'location', 'categories'],
        ];

        if (isset($typeRules[$record->type])) {
            $fields = $typeRules[$record->type];
            $total += count($fields);
            foreach ($fields as $field) {
                if (!empty($metadata[$field])) $score++;
            }
        }

        return round(($score / $total) * 100);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProfiles::route('/'),
        ];
    }
}
