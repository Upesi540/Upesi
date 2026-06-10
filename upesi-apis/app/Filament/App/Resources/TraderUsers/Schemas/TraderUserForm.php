<?php

namespace App\Filament\App\Resources\TraderUsers\Schemas;

use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class TraderUserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isEdit = $schema->getOperation() === 'edit';
        $record = $schema->getRecord();

        // Conditions pour la modification
        $isApproved = $record && $record->is_approved;
        $isEmailVerified = $record && $record->hasVerifiedEmail();

        // L'email est modifiable seulement si : non approuvé ET email non vérifié
        $canEditEmail = !$isApproved && !$isEmailVerified;

        // Le mot de passe est modifiable seulement si : non approuvé
        $canEditPassword = !$isApproved;

        // Les infos de base sont modifiables seulement si : non approuvé
        $canEditBasicInfo = !$isApproved;

        return $schema
            ->columns(3)
            ->components([
                // Section Informations personnelles
                Section::make('Informations personnelles')
                    ->icon('heroicon-o-user')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255)
                            ->disabled($isEdit && !$canEditBasicInfo),

                        TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->disabled($isEdit && !$canEditBasicInfo),

                        TextInput::make('email')
                            ->label('Adresse email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled($isEdit && !$canEditEmail)
                            ->helperText(function() use ($isEdit, $isEmailVerified, $isApproved) {
                                if (!$isEdit) return '';
                                if ($isApproved) return '❌ Compte approuvé - email modifiable uniquement par l\'admin';
                                if ($isEmailVerified) return '⚠️ Email déjà vérifié - modification non autorisée';
                                return '✅ Email modifiable tant qu\'il n\'est pas vérifié';
                            }),

                        TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->disabled($isEdit && !$canEditBasicInfo),

                        Select::make('country_id')
                            ->label('Pays')
                            ->relationship('country', 'name')
                            ->searchable(['name', 'iso2'])
                            ->preload()
                            ->required()
                            ->disabled($isEdit) // Le pays ne change jamais
                            ->helperText('Le pays ne peut pas être modifié après création'),
                    ]),

                // Section Type de compte (UNIQUEMENT à la création)
                Section::make('Type de compte')
                    ->icon('heroicon-o-briefcase')
                    ->description('Choisissez le type de compte à créer')
                    ->columnSpan(1)
                    ->visible(!$isEdit)
                    ->schema([
                        Select::make('merchant_type')
                            ->label('Je crée un compte')
                            ->options([
                                'producer' => '🌾 Producteur',
                                'supplier' => '📦 Fournisseur',
                                'provider' => '🛠️ Prestataire',
                                'transporter' => '🚚 Transporteur',
                                'customer' => '👤 Client',
                            ])
                            ->required()
                            ->reactive()
                            ->helperText('Le type de compte détermine les fonctionnalités disponibles'),
                    ]),

                // Section Statut
                Section::make('Statut du compte')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Compte actif')
                            ->default(true)
                            ->disabled()
                            ->helperText('Géré par l\'administrateur'),

                        // Afficher les statuts en modification
                        Toggle::make('is_approved')
                            ->label('Approuvé par l\'admin')
                            ->disabled()
                            ->visible($isEdit)
                            ->helperText('Seul l\'administrateur peut approuver'),

                        // Afficher si l'email est vérifié
                        Toggle::make('email_verified_at')
                            ->label('Email vérifié')
                            ->disabled()
                            ->visible($isEdit)
                            ->formatStateUsing(fn($state) => !is_null($state))
                            ->helperText('L\'utilisateur doit vérifier son email'),
                    ]),

                // Section Avatar
                Section::make('Avatar')
                    ->icon('heroicon-o-camera')
                    ->columnSpan(2)
                    ->compact()
                    ->schema([
                        FileUpload::make('profile_photo_path')
                            ->label('Photo de profil')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->directory('avatars/' . date('Y/m'))
                            ->maxSize(1024)
                            ->helperText('Optionnel - Format JPG ou PNG, max 1MB')
                            ->disabled($isEdit && !$canEditBasicInfo),
                    ]),

                // Section Mot de passe
                Section::make('Sécurité')
                    ->icon('heroicon-o-key')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('password')
                            ->label(function() use ($isEdit, $canEditPassword) {
                                if (!$isEdit) return 'Mot de passe temporaire';
                                if (!$canEditPassword) return 'Mot de passe (modification bloquée)';
                                return 'Nouveau mot de passe (optionnel)';
                            })
                            ->password()
                            ->revealable()
                            ->rule(Password::default())
                            ->required(fn() => !$isEdit)
                            ->disabled($isEdit && !$canEditPassword)
                            ->helperText(function() use ($isEdit, $isApproved, $canEditPassword) {
                                if (!$isEdit) {
                                    return 'L\'utilisateur devra changer son mot de passe à la première connexion';
                                }
                                if ($isApproved) {
                                    return '❌ Compte approuvé - contacter l\'admin pour modifier le mot de passe';
                                }
                                if ($canEditPassword) {
                                    return '✅ Laissez vide pour conserver le mot de passe actuel';
                                }
                                return 'Modification non autorisée';
                            }),
                    ]),

                // Champs cachés
                Hidden::make('created_by')
                    ->default(fn() => Auth::id()),

                Hidden::make('is_approved')
                    ->default(false),

                Hidden::make('email_verified_at')
                    ->default(null),
            ]);
    }
}
