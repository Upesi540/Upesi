<?php

namespace App\Filament\Resources\KycVerifications;

use App\Filament\Resources\KycVerifications\Pages\ManageKycVerifications;
use App\Models\KycVerification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class KycVerificationResource extends Resource
{
    protected static ?string $model = KycVerification::class;
    protected static ?string $modelLabel = 'Vérification KYC';
    protected static ?string $pluralModelLabel = 'Vérifications KYC';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;
    protected static string|UnitEnum|null $navigationGroup = 'Conformité & Sécurité';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Détails du Compte')
                ->schema([
                    Select::make('user_id')
                        ->label('Utilisateur')
                        ->relationship('user', 'last_name')
                        ->disabled(),
                    Select::make('entity_type')->label('Type d\'entité')
                        ->options([
                            'individual' => 'Un Particulier / Planteur',
                            'business' => 'Une Entreprise / Boutique',
                        ])->disabled(),
                    Select::make('document_type')
                        ->label('Type de pièce d\'identité')
                        ->options([
                            'cni' => 'Carte Nationale d\'Identité',
                            'passport' => 'Passeport',
                            'planter_card' => 'Carte de Planteur',
                        ])->disabled(),
                    TextInput::make('document_number')->label('Numéro de pièce')->disabled(),
                    TextInput::make('expiry_date')->label('Date d\'expiration')->disabled(),
                ])->columns(2),

            Section::make('Documents d\'Identité & Selfie')
                ->description('Vérifiez la correspondance entre le selfie et la pièce d\'identité.')
                ->schema([
                    FileUpload::make('document_files')
                        ->label('Recto / Verso')
                        ->multiple()
                        ->image()
                        ->directory('kyc-private')
                        ->visibility('private')
                        ->openable()

                        ->disabled(),
                    FileUpload::make('selfie_path')
                        ->label('Selfie de vérification')
                        ->directory('kyc-private')
                        ->visibility('private')
                        ->image()
                        ->openable()
                        ->disabled(),
                ])->columns(2),
            Section::make('Profils Marchands (Upesi)')
                ->description('Types de comptes actifs pour cet utilisateur.')
                ->schema([
                    Placeholder::make('user_profiles')
                        ->label('Profils enregistrés')
                        ->content(function ($record) {
                            // On utilise ta relation merchantProfiles
                            $profiles = $record->user->merchantProfiles->pluck('type')->unique();

                            if ($profiles->isEmpty()) {
                                return new HtmlString('<span class="text-gray-500 italic">Aucun profil marchand actif</span>');
                            }

                            $badges = $profiles->map(function ($type) {
                                // Mapping des couleurs et labels en français
                                [$label, $color] = match ($type) {
                                    'producer'    => ['PRODUCTEUR / PLANTEUR', '#22c55e'], // Vert
                                    'provider'    => ['PRESTATAIRE', '#3b82f6'],           // Bleu
                                    'transporter' => ['TRANSPORTEUR', '#f59e0b'],         // Orange
                                    'trader'      => ['COMMERÇANT / BOUTIQUE', '#8b5cf6'], // Violet
                                    'supplier'    => ['FOURNISSEUR', '#ec4899'],          // Rose
                                    default       => [strtoupper($type), '#6b7280'],      // Gris
                                };

                                return "<span style='background-color: {$color}22; color: {$color}; padding: 3px 12px; border-radius: 9999px; font-weight: 800; font-size: 0.7rem; border: 1px solid {$color}55; margin-right: 4px; display: inline-block;'>
                                {$label}
                            </span>";
                            })->implode('');

                            return new HtmlString("<div class='flex flex-wrap gap-y-2'>{$badges}</div>");
                        })
                ]),
            Section::make('Documents Business (Gabon)')
                ->description('Informations légales de l\'entreprise ou du commerce.')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('rccm_number')->label('Numéro RCCM')->disabled(),
                        TextInput::make('nif_number')->label('Numéro NIF')->disabled(),
                    ]),
                    Grid::make(1)->schema([
                        FileUpload::make('rccm_path')->label('Document RCCM')->directory('kyc-business')
                            ->visibility('private')->openable()
                            ->disabled(),
                        FileUpload::make('cfe_card_path')->label('Carte CFE')->directory('kyc-business')
                            ->visibility('private')->openable()
                            ->disabled(),
                        FileUpload::make('quitus_fiscal_path')->label('Quitus Fiscal')->directory('kyc-business')
                            ->visibility('private')->openable()
                            ->disabled(),
                    ]),
                ]),

            Section::make('Localisation du Magasin')
                ->schema([
                    Textarea::make('store_description')->label('Adresse / Description')->disabled()->columnSpanFull(),
                    TextInput::make('google_maps_url')->label('Lien Google Maps')->disabled(),
                    TextInput::make('latitude')->disabled(),
                    TextInput::make('longitude')->disabled(),
                ])->columns(3),

            Section::make('Décision Admin')
                ->schema([
                    Select::make('status')
                        ->options([
                            'pending' => 'En attente',
                            'approved' => 'Approuvé',
                            'rejected' => 'Rejeté',
                        ])
                        ->required()
                        ->native(false)
                        ->live() // Indispensable pour que le formulaire réagisse au changement
                        ->afterStateUpdated(function (string $state, $set) {
                            // Si on approuve, on vide automatiquement les notes
                            if ($state === 'approved') {
                                $set('admin_notes', null);
                            }
                        }),

                    Textarea::make('admin_notes')
                        ->label('Commentaires / Raison du rejet')
                        ->placeholder('Indiquez ici pourquoi le dossier est rejeté (ex: Photo floue)...')
                        // Option 1 : On le masque complètement si c'est approuvé
                        ->hidden(fn($get) => $get('status') === 'approved')
                        // Option 2 : On le rend obligatoire uniquement si c'est rejeté
                        ->required(fn($get) => $get('status') === 'rejected')
                        ->dehydrated() // Force l'envoi de la valeur même si le champ est masqué
                        ->columnSpanFull(),
                ])
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

                // Liste des types de profils marchands séparés par des virgules
                TextColumn::make('user.merchantProfiles.type')
                    ->label('Profils Marchands')
                    ->listWithLineBreaks(false) // On reste sur une ligne
                    ->separator(', ') // Séparateur par virgule
                    ->badge() // Optionnel : met chaque type dans un petit badge pour la lisibilité
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'producer'    => 'Producteur',
                        'provider'    => 'Prestataire',
                        'transporter' => 'Transporteur',
                        'trader'      => 'Commerçant',
                        'supplier'    => 'Fournisseur',
                        default       => $state,
                    })
                    ->color('gray'),
                TextColumn::make('entity_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'individual' => 'Particulier / Planteur',
                        'business' => 'Entreprise / Boutique',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'individual' => 'info',
                        'business' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('document_type')
                    ->label('Pièce')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cni' => 'Carte d\'Identité',
                        'passport' => 'Passeport',
                        'planter_card' => 'Carte de Planteur',
                        default => $state,
                    }),
                IconColumn::make('status')
                    ->options([
                        'heroicon-m-clock' => 'pending',
                        'heroicon-m-check-circle' => 'approved',
                        'heroicon-m-x-circle' => 'rejected',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Soumis le')
                    ->sortable(),
            ])
            ->recordActions([
                // --- C'EST ÇA QUI MANQUE ---
                // Cette action va ouvrir une modal avec tout ton Schema (le formulaire)
                EditAction::make()
                    ->label('Examiner')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->modalHeading('Examen du dossier KYC')
                    ->modalWidth('7xl'),
                // Bouton rapide d'approbation
                Action::make('approve')
                    ->label('Approuver')
                    ->action(function (KycVerification $record) {
                        $record->update([
                            'status' => 'approved',
                            'admin_notes' => null,
                            'verified_at' => now(),
                        ]);
                        // On active TOUS les profils marchands de l'utilisateur d'un coup
                        $record->user->merchantProfiles()->update([
                            'is_active' => true,
                            'validated_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-m-check')
                    ->visible(fn($record) => $record->status === 'pending'),

                // Bouton de rejet rapide
                Action::make('reject')
                    ->label('Rejeter')
                    ->form([
                        Textarea::make('admin_notes')
                            ->label('Motif du rejet')
                            ->required(),
                    ])
                    ->action(function (KycVerification $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'admin_notes' => $data['admin_notes'],
                        ]);
                    })
                    ->color('danger')
                    ->icon('heroicon-m-x-mark')
                    ->visible(fn($record) => $record->status === 'pending'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageKycVerifications::route('/'),
        ];
    }
}
