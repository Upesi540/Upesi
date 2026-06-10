<?php

namespace App\Filament\App\Resources\TraderUsersMerchantProfiles;

use App\Filament\App\Resources\TraderUsersMerchantProfiles\Pages\ManageTraderUsersMerchantProfiles;
use App\Models\Category;
use App\Models\Crop;
use App\Models\MerchantProfile;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use App\Models\VehicleType;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class TraderUsersMerchantProfileResource extends Resource
{
    use InteractsWithForms;

    protected static ?string $model = MerchantProfile::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Profils de mes affiliés';
    protected static ?string $modelLabel = 'Profil affilié';
    protected static ?string $pluralModelLabel = 'Profils affiliés';
    protected static string|UnitEnum|null $navigationGroup = 'Gestion affiliés';
    protected static ?int $navigationSort = 2;

    /**
     * 🔥 Récupère l'utilisateur connecté de manière fiable dans Filament
     */
    protected static function getCurrentUser(): ?User
    {
        return filament()->auth()->user();
    }

    /**
     * 🔥 Vérifie si l'utilisateur est un trader
     */
    protected static function isTrader(?User $user = null): bool
    {
        $user = $user ?? static::getCurrentUser();

        if (!$user) {
            return false;
        }

        return $user->merchantProfiles()
            ->where('type', 'trader')
            ->exists();
    }

    /**
     * 🔥 FILTRE : Le trader ne voit que les profils des utilisateurs QU'IL A CRÉÉS
     */
    public static function getEloquentQuery(): Builder
    {
        $user = static::getCurrentUser();

        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        $isTrader = static::isTrader($user);

        if ($isTrader) {
            return parent::getEloquentQuery()
                ->whereHas('user', function($query) use ($user) {
                    $query->where('created_by', $user->id);
                });
        }

        return parent::getEloquentQuery();
    }

    /**
     * 🔥 ACCÈS : Seul le trader ou l'admin peut accéder
     */
    public static function canAccess(): bool
    {
        $user = static::getCurrentUser();

        if (!$user) {
            return false;
        }

        return $user->hasRole('super_admin')
            || $user->hasRole('admin')
            || static::isTrader($user);
    }

    public static function form(Schema $schema): Schema
    {
        $user = static::getCurrentUser();
        $isTrader = $user ? static::isTrader($user) : false;

        return $schema
            ->schema([
                Section::make('Statut du profil')
                    ->description('Ce profil est en attente de validation administrative')
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('status')
                                ->label('Statut')
                                ->options([
                                    'pending' => 'En attente',
                                    'approved' => 'Approuvé',
                                    'rejected' => 'Rejeté',
                                ])
                                ->disabled($isTrader)
                                ->required()
                                ->native(false),
                        ]),
                    ])->columns(1),

                Tabs::make('Profile Tabs')
                    ->tabs([
                        Tabs\Tab::make('Informations Boutique')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Détails du Compte')
                                    ->aside()
                                    ->schema([
                                        Select::make('user_id')
                                            ->relationship('user')
                                            ->label('Propriétaire du compte')
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                                            ->searchable(['first_name', 'last_name', 'email'])
                                            ->options(function() use ($isTrader, $user) {
                                                if ($isTrader && $user) {
                                                    return User::where('created_by', $user->id)
                                                        ->get()
                                                        ->mapWithKeys(fn($u) => [$u->id => "{$u->first_name} {$u->last_name} ({$u->email})"]);
                                                }
                                                return User::all()->mapWithKeys(fn($u) => [$u->id => "{$u->first_name} {$u->last_name} ({$u->email})"]);
                                            })
                                            ->disabled(fn($record) => $record !== null)
                                            ->columnSpanFull()
                                            ->prefixIcon('heroicon-m-user-circle'),

                                        TextInput::make('shop_name')
                                            ->label('Nom de la boutique')
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-shopping-bag'),

                                        Grid::make(2)->schema([
                                            Select::make('type')
                                                ->label('Type d\'activité')
                                                ->options(MerchantProfile::TYPESWITHOUTTRADER)
                                                ->required()
                                                ->native(false)
                                                ->disabled(fn($record) => $record !== null)
                                                ->unique(
                                                    table: 'merchant_profiles',
                                                    column: 'type',
                                                    ignorable: fn($record) => $record,
                                                    modifyRuleUsing: function ($rule, $get) {
                                                        return $rule->where('user_id', $get('user_id'));
                                                    }
                                                )
                                                ->validationMessages([
                                                    'unique' => 'Cet utilisateur a déjà un profil de ce type.',
                                                ]),

                                            PhoneInput::make('phone')
                                                ->label('Téléphone')
                                                ->prefixIcon('heroicon-m-phone')
                                        ]),

                                        Textarea::make('description')
                                            ->label('Description')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tabs\Tab::make('Détails Professionnels')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Section::make('Spécifications Métier')
                                    ->aside()
                                    ->schema([
                                        // BLOC PRODUCTEUR
                                        Grid::make(2)->visible(fn($get) => $get('type') === 'producer')->schema([
                                            TextInput::make('metadata.address')->label('Adresse de l\'exploitation')->required(),
                                            Select::make('metadata.location')->label('Région')->options(State::pluck('name', 'id'))->required()->native(false),
                                            TextInput::make('metadata.surface')->label('Superficie')->numeric()->suffix('ha'),
                                            Select::make('metadata.crops')->label('Spéculations')->options(Crop::pluck('name', 'id'))->multiple()->maxItems(3),
                                        ]),

                                        Grid::make(2)->visible(fn($get) => false)->schema([
                                            TextInput::make('metadata.address')->label('Adresse commerciale')->required(),
                                            Select::make('metadata.location')->label('Région')->searchable()->options(State::pluck('name', 'id'))->required(),
                                            Select::make('metadata.crops')->label('Produits tradés')->options(Crop::pluck('name', 'id'))->multiple(),
                                        ]),

                                        // BLOC TRANSPORTEUR
                                        Grid::make(2)->visible(fn($get) => $get('type') === 'transporter')->schema([
                                            TextInput::make('metadata.address')->label('Siège social')->required(),
                                            Select::make('metadata.location')->searchable()->label('Région de base')->options(State::pluck('name', 'id'))->required(),
                                            Select::make('metadata.transport_types')
                                                ->label('Types de transport')
                                                ->options(
                                                    Service::whereHas('category', function ($q) {
                                                        $q->where('slug', 'logistique');
                                                    })->pluck('name', 'id')
                                                )
                                                ->multiple()
                                                ->searchable()
                                                ->required(),
                                            Select::make('metadata.vehicle_type')->label('Moyens de transport')->options(VehicleType::pluck('name', 'id'))->multiple(),
                                        ]),

                                        // BLOC PRESTATAIRE
                                        Grid::make(2)->visible(fn($get) => $get('type') === 'provider')->schema([
                                            TextInput::make('metadata.personal_address')->label('Adresse personnelle')->required(),
                                            TextInput::make('metadata.company_address')->label('Adresse entreprise')->required(),
                                            Select::make('metadata.service_zone')->searchable()->label('Zones d\'intervention')->options(State::pluck('name', 'id'))->multiple(),
                                            Select::make('metadata.service_type')->searchable()->label('Types de services')->options(Service::pluck('name', 'id'))->multiple(),
                                        ]),

                                        // BLOC FOURNISSEUR
                                        Grid::make(2)->visible(fn($get) => $get('type') === 'supplier')->schema([
                                            TextInput::make('metadata.personal_address')->label('Adresse personnelle')->required(),
                                            TextInput::make('metadata.company_address')->label('Adresse société')->required(),
                                            Select::make('metadata.location')->searchable()->label('Localisation principale')->options(State::pluck('name', 'id'))->required(),
                                            Select::make('metadata.categories')->searchable()->label('Catégories produits')->options(Category::pluck('name', 'id'))->multiple(),
                                        ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('user.first_name')
                    ->label('Utilisateur')
                    ->weight('bold')
                    ->sortable()
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->formatStateUsing(fn($record): string => "{$record->user->first_name} {$record->user->last_name}")
                    ->description(fn($record): string => $record->user->email)
                    ->copyable()
                    ->copyMessage('Email copié !'),

                TextColumn::make('shop_name')
                    ->label('Boutique')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn($record) => "Type: " . (MerchantProfile::TYPES[$record->type] ?? $record->type)),

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
                    ->suffix('%')
                    ->color(fn($state) => (int) $state < 100 ? 'warning' : 'success'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(MerchantProfile::TYPES),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvés',
                        'rejected' => 'Rejetés',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    /**
     * Calcul du taux de complétion du profil
     */
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTraderUsersMerchantProfiles::route('/'),
        ];
    }
}
