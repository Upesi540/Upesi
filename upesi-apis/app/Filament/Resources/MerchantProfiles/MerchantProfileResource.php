<?php

namespace App\Filament\Resources\MerchantProfiles; // Changement de namespace pour le panel admin

use App\Filament\Resources\MerchantProfiles\Pages\ManageMerchantProfiles;
use App\Models\Category;
use App\Models\Crop;
use App\Models\Market;
use App\Models\MerchantProfile;
use App\Models\Service;
use App\Models\State;
use App\Models\TransportType;
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
use Filament\Tables\Columns\SelectColumn; // Pour changer le statut rapidement
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;

class MerchantProfileResource extends Resource
{
    use InteractsWithForms;

    protected static ?string $model = MerchantProfile::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    // Libellés adaptés à l'admin
    protected static ?string $navigationLabel = 'Gestion des Profils marchands';
    protected static ?string $modelLabel = 'Profil Marchand';
    protected static ?string $pluralModelLabel = 'Profils Marchands';
    protected static string|UnitEnum|null $navigationGroup = 'Comptes & Permissions';
    protected static ?int $navigationSort = 2; // Pour le mettre juste après la gestion des Users (souvent à 1)
    /**
     * Suppression du getEloquentQuery restrictif
     * L'admin doit voir TOUS les profils.
     */

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // Ajout d'une section de validation pour l'Admin en haut
                Section::make('Validation Administrative')
                    ->description('Décidez si ce profil est autorisé à opérer sur Upesi.')
                    ->schema([
                        Grid::make(1)->schema([
                            Select::make('status')
                                ->label('Statut de validation')
                                ->options([
                                    'pending' => 'En attente',
                                    'approved' => 'Approuver le profil',
                                    'rejected' => 'Rejeter le profil',
                                ])
                                ->required()
                                ->native(false),
                        ]),
                    ])->columns(1),

                Tabs::make('Profile Tabs')
                    ->tabs([
                        // --- ONGLET 1 : IDENTITÉ ---
                        Tabs\Tab::make('Informations Boutique')
                            ->icon('heroicon-o-building-storefront')
                            ->schema([
                                Section::make('Détails du Compte')
                                    ->aside()
                                    ->schema([
                                        // On affiche l'utilisateur propriétaire (Lecture seule pour l'admin)
                                        Select::make('user_id')
                                            ->relationship('user') // On retire le 'name' ici pour personnaliser l'affichage
                                            ->label('Propriétaire du compte')
                                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                                            ->searchable(['first_name', 'last_name', 'email']) // L'admin peut chercher par nom ou email
                                            ->disabled() // On garde le disabled car on ne change pas le proprio d'un profil
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
                                                ->options(MerchantProfile::TYPES)
                                                ->required()
                                                ->native(false)
                                                ->disabled(fn($record) => $record !== null) // Désactivé en édition seulement
                                                ->unique(
                                                    table: 'merchant_profiles',
                                                    column: 'type',
                                                    ignorable: fn($record) => $record,
                                                    modifyRuleUsing: function ($rule, $get) {
                                                        // Vérifier l'unicité pour l'utilisateur sélectionné
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

                        // --- ONGLET 2 : DÉTAILS TECHNIQUES (Ta logique v5 conservée) ---
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

                                        // BLOC NEGOCIANT
                                        Grid::make(2)->visible(fn($get) => $get('type') === 'trader')->schema([
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
                                                        $q->where('slug', 'logistique'); // ou where('name', 'Transport')
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
                TextColumn::make('user.first_name') // On part du prénom
                    ->label('Utilisateur')
                    ->weight('bold')
                    ->sortable()
                    ->searchable(['first_name', 'last_name', 'email']) // Permet de chercher par les 3 champs
                    ->formatStateUsing(fn($record): string => "{$record->user->first_name} {$record->user->last_name}")
                    ->description(fn($record): string => $record->user->email) // L'email s'affiche en petit gris en dessous
                    ->copyable() // Optionnel : pratique pour l'admin s'il doit copier l'email rapidement
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
                // DeleteAction::make(),
            ]);
    }

    // Conservation de ta logique de calcul de complétion v5
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
            'index' => ManageMerchantProfiles::route('/'), // Si tu utilises une simple table
        ];
    }
}
