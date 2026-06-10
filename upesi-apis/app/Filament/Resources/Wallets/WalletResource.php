<?php

namespace App\Filament\Resources\Wallets;

use App\Filament\Resources\Wallets\Pages\ManageWallets;
use App\Models\MerchantProfile;
use App\Models\Wallet;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    // Groupement dans le menu de navigation
    protected static string|UnitEnum|null $navigationGroup = 'Trésorerie';

    protected static ?string $modelLabel = 'Portefeuille';
    protected static ?string $pluralModelLabel = 'Portefeuilles';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('currencies', 'wallets.currency_id', '=', 'currencies.id')
            ->select('wallets.*') // Évite les conflits de colonnes
            ->orderBy('wallets.created_at', 'desc');
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('holder_type')
                    ->label('Type de compte')
                    ->options([
                        'user' => 'Compte Utilisateur',
                        'system_commission' => 'Commission Upesi',
                        'system_escrow' => 'Séquestre (Transit)',
                    ])
                    ->required()
                    ->live() // Indispensable pour que le champ user_id apparaisse dynamiquement
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),
                Select::make('user_id')
                    ->label('Propriétaire ')
                    ->relationship('user') // On retire 'name' pour personnaliser l'affichage
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->first_name} {$record->last_name} ({$record->email})")
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->preload()
                    ->prefixIcon('heroicon-m-user')

                    // Visibilité et obligation basées sur le type de détenteur
                    ->visible(fn($get) => $get('holder_type') === 'user')
                    ->required(fn($get) => $get('holder_type') === 'user')

                    // Sécurité : Impossible de changer le proprio une fois créé
                    ->disabled(fn(string $operation): bool => $operation === 'edit')

                    ->columnSpanFull(),

                Select::make('currency_id')
                    ->label('Devise')
                    ->relationship(
                        name: 'currency',
                        titleAttribute: 'code',
                        modifyQueryUsing: fn(Builder $query) => $query->orderBy('code')
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->code} - {$record->name}")
                    ->searchable(['code', 'name'])
                    ->preload()
                    ->required()
                    ->disabled(fn(string $operation): bool => $operation === 'edit'),

                TextInput::make('available_balance')
                    ->label('Solde Disponible')
                    ->numeric()
                    ->prefix('Σ')
                    ->disabled()
                    ->default(0.0),

                TextInput::make('frozen_balance')
                    ->label('Solde Gelé')
                    ->numeric()
                    ->prefix('❄️')
                    ->disabled()
                    ->default(0.0),

                Toggle::make('is_active')
                    ->label('Compte Actif')
                    ->onColor('success')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->columns([
                TextColumn::make('holder_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'system_commission' => 'COMMISSION',
                        'system_escrow' => 'SÉQUESTRE',
                        default => 'UTILISATEUR',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'system_commission' => 'success',
                        'system_escrow' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('user.merchantProfiles.type')
                    ->label('Rôles Marchands')
                    ->badge()
                    ->separator(',') // Important : transforme la collection en badges séparés
                    ->formatStateUsing(fn(string $state): string => MerchantProfile::TYPES[$state] ?? $state)
                    ->colors([
                        'success' => 'producer',
                        'info'    => 'trader',
                        'warning' => 'transporter',
                        'primary' => 'provider',
                        'gray'    => 'supplier',
                    ])
                    ->icons([
                        'heroicon-o-tag'                  => 'producer',
                        'heroicon-o-shopping-bag'         => 'trader',
                        'heroicon-o-truck'                => 'transporter',
                        'heroicon-o-wrench-screwdriver'   => 'provider',
                        'heroicon-o-cube'                 => 'supplier',
                    ])
                    ->placeholder('Client')
                    ->searchable()
                    ->wrap(), // Permet de passer à la ligne s'il y a trop de badges
                TextColumn::make('user.first_name') // On part du prénom
                    ->label('Utilisateur')
                    ->weight('bold')
                    ->sortable()
                    ->searchable(['first_name', 'last_name', 'email']) // Permet de chercher par les 3 champs
                    ->formatStateUsing(fn($record): string => "{$record->user->first_name} {$record->user->last_name}")
                    ->description(fn($record): string => $record->user->email) // L'email s'affiche en petit gris en dessous
                    ->copyable() // Optionnel : pratique pour l'admin s'il doit copier l'email rapidement
                    ->copyMessage('Email copié !')
                    ->placeholder('Plateforme Upesi'),

                TextColumn::make('available_balance')
                    ->label('Disponible')
                    ->money(fn($record) => $record->currency->code ?? 'USD')
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('frozen_balance')
                    ->label('Gelé')
                    ->money(fn($record) => $record->currency->code ?? 'USD')
                    ->color('warning')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Masqué par défaut pour épurer

                TextColumn::make('currency.code')
                    ->label('Devise')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_active')
                    ->label('Statut')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('holder_type')
                    ->label('Filtrer par type')
                    ->options([
                        'user' => 'Comptes Utilisateurs',
                        'system_commission' => 'Gains Upesi',
                        'system_escrow' => 'Séquestres',
                    ]),
                SelectFilter::make('currency_id')
                    ->label('Devise')
                    ->relationship('currency', 'code'),
            ])
            ->actions([
                // Action pour simuler ton "Gérer" du RelationManager
                EditAction::make()
                    ->label('Détails')
                    ->modalHeading('Aperçu du portefeuille')
                    ->icon('heroicon-m-eye')
                    ->color('info'),

                // Optionnel : Action pour ajuster le solde manuellement avec une trace
                // Action::make('ajuster_solde')
                //     ->label('Ajuster')
                //     ->icon('heroicon-m-calculator')
                //     ->color('warning')
                //     ->requiresConfirmation()
                // Ici tu pourrais ajouter un formulaire pour les dépôts manuels
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWallets::route('/'),
        ];
    }
}
