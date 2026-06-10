<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // Section Informations personnelles
                Section::make('Informations personnelles')
                    ->icon('heroicon-o-user')
                    ->columnSpan(2)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('Prénom')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Jean'),

                                TextInput::make('last_name')
                                    ->label('Nom')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('DUPONT'),

                                TextInput::make('email')
                                    ->label('Adresse email')
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('jean.dupont@exemple.fr'),

                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(20)
                                    ->placeholder('+33 6 12 34 56 78'),
                                Select::make('country_id')
                                    ->label('Pays')
                                    ->relationship(
                                        name: 'country',
                                        titleAttribute: 'name',
                                    )
                                    // Permet de chercher par code OU par nom
                                    ->getOptionLabelFromRecordUsing(fn($record) => "
                                    <div style='display: flex; align-items: center; gap: 8px;'>
                                        <img src='https://flagcdn.com/w20/" . strtolower($record->iso2) . ".png' style='height: 14px; width: auto;' />
                                        <span>{$record->name}</span>
                                    </div>
                                    ")->allowHtml()
                                    ->searchable(['name', 'iso2'])
                                    ->preload()
                                    ->disabled(fn(string $operation): bool => $operation === 'edit'),

                            ]),
                    ]),

                // Section Statuts du compte
                Section::make('Statuts du compte')
                    ->icon('heroicon-o-shield-check')
                    ->description('Gestion des permissions et accès')
                    ->columnSpan(1)
                    ->schema([
                        Fieldset::make('État du compte')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Compte actif')
                                    ->default(true)
                                    ->helperText('Désactiver pour bloquer l\'accès au compte')
                                    ->onIcon('heroicon-o-check-circle')
                                    ->offIcon('heroicon-o-x-circle')
                                    ->onColor('success')
                                    ->offColor('danger'),

                                // Toggle::make('is_approved')
                                //     ->label('Compte approuvé')
                                //     ->default(false)
                                //     ->helperText('Validation administrative requise pour certains statuts')
                                //     ->onIcon('heroicon-o-check-badge')
                                //     ->offIcon('heroicon-o-clock')
                                //     ->onColor('success')
                                //     ->offColor('warning'),

                                Toggle::make('is_banned')
                                    ->label('Compte banni')
                                    ->default(false)
                                    ->helperText('Bannissement total du compte')
                                    ->onIcon('heroicon-o-no-symbol')
                                    ->offIcon('heroicon-o-check')
                                    ->onColor('danger')
                                    ->offColor('success')
                                    ->reactive()
                                    ->afterStateUpdated(function ($set, $state) {
                                        if ($state) {
                                            $set('is_active', false);
                                        }
                                    }),

                                Toggle::make('has_email_authentication')
                                    ->label('Authentification par email')
                                    ->default(false)
                                    ->helperText('Activer l\'authentification via email')
                                    ->onIcon('heroicon-o-lock-closed')
                                    ->offIcon('heroicon-o-lock-open'),
                            ]),
                    ]),
                // Dans UserResource.php
                Section::make('Préférences Financières')
                    ->description('Configuration des devises et portefeuilles par défaut.')
                    ->schema([
                        Select::make('preferred_currency_id')
                            ->relationship('preferredCurrency', 'code')
                            ->label('Devise péférée de l\'utilisateur')
                            ->helperText('Utilisée pour afficher à l\'user.')
                            ->disabled(), // Généralement lié au pays, donc readonly

                    ])->columnspan(1),

                // Section Avatar
                Section::make('Avatar')
                    ->icon('heroicon-o-camera')
                    ->description('Photo de profil')
                    ->columnspan(2)
                    ->compact()
                    ->schema([
                        FileUpload::make('profile_photo_path')
                            ->label('Photo de profil')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->imageAspectRatio('1:1') // Format carré pour avatar
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Formats acceptés : JPG, PNG, WebP. Taille max : 2MB')
                            ->placeholder('Cliquez pour télécharger un avatar')
                            ->nullable(),
                    ]),

                // Section Sécurité - Mot de passe
                Section::make('Sécurité - Mot de passe')
                    ->icon('heroicon-o-key')
                    ->description('Gestion du mot de passe utilisateur')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('password')
                                    ->label('Nouveau mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->rule(Password::default())
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(string $context): bool => $context === 'create')
                                    ->helperText(
                                        fn(string $context): string =>
                                        $context === 'create'
                                            ? 'Requis pour un nouvel utilisateur'
                                            : 'Laissez vide pour conserver le mot de passe actuel'
                                    )
                                    ->extraInputAttributes(['autocomplete' => 'new-password']),

                                TextInput::make('passwordConfirmation')
                                    ->label('Confirmer le mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->same('password')
                                    ->dehydrated(false)
                                    ->required(fn($get) => filled($get('password')))
                                    ->helperText('Doit correspondre au nouveau mot de passe'),

                                Toggle::make('send_password_notification')
                                    ->label('Notifier l\'utilisateur')
                                    ->default(false)
                                    ->visible(fn(string $context): bool => $context === 'edit' && filled(request()->route('record')))
                                    ->helperText('Envoyer un email avec le nouveau mot de passe')
                                    ->dehydrated(false),
                            ]),
                    ]),

                // Section 2FA
                Section::make('Authentification à deux facteurs (2FA)')
                    ->icon('heroicon-o-shield-exclamation')
                    ->description('Configuration de la double authentification')
                    ->columnSpanFull()
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('app_authentication_secret')
                                    ->label('Secret 2FA')
                                    ->placeholder('Laisser vide pour ne pas modifier')
                                    ->helperText('Modifier uniquement si nécessaire (format texte)')
                                    ->maxLength(65535),

                                Textarea::make('app_authentication_recovery_codes')
                                    ->label('Codes de récupération')
                                    ->placeholder('Codes de récupération JSON')
                                    ->helperText('Codes au format JSON (générés automatiquement si besoin)')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Toggle::make('reset_2fa')
                                    ->label('Réinitialiser la 2FA')
                                    ->default(false)
                                    ->helperText('Réinitialiser complètement la configuration 2FA')
                                    ->dehydrated(false)
                                    ->afterStateUpdated(function ($set, $state) {
                                        if ($state) {
                                            $set('app_authentication_secret', null);
                                            $set('app_authentication_recovery_codes', null);
                                        }
                                    }),
                            ]),
                    ])
                    ->visible(fn(string $context): bool => $context === 'edit'),

                // Section Suppression/Bannissement
                Section::make('Modération - Suppression & Bannissement')
                    ->icon('heroicon-o-trash')
                    ->description('Actions de modération avancées')
                    ->columnSpanFull()
                    ->collapsed()
                    ->visible(fn(string $context): bool => $context === 'edit')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // Soft Delete
                                Group::make()
                                    ->schema([
                                        Toggle::make('mark_for_deletion')
                                            ->label('Marquer pour suppression')
                                            ->default(fn($record) => $record && $record->trashed())
                                            ->reactive()
                                            ->afterStateUpdated(function ($set, $state, $get) {
                                                if ($state) {
                                                    $set('deleted_at', now());
                                                    $set('is_active', false);
                                                } else {
                                                    $set('deleted_at', null);
                                                }
                                            })
                                            ->helperText('Supprimer (soft delete) ce compte'),

                                        DateTimePicker::make('deleted_at')
                                            ->label('Date de suppression')
                                            ->visible(fn($get) => $get('mark_for_deletion')),
                                    ]),

                                // Raison
                                Group::make()
                                    ->schema([
                                        Textarea::make('deletion_reason')
                                            ->label('Raison de la suppression/bannissement')
                                            ->placeholder('Expliquez la raison...')
                                            ->rows(3)
                                            ->columnSpan(2)
                                            ->helperText('Visible par les administrateurs uniquement'),
                                    ])
                                    ->columnSpan(2),

                                // Supprimé par
                                Select::make('deleted_by')
                                    ->label('Supprimé par')
                                    ->options(User::query()->pluck('first_name', 'id'))
                                    ->searchable()
                                    ->nullable()
                                    ->default(Auth::user()?->id)
                                    ->visible(fn($get) => $get('mark_for_deletion')),
                            ]),
                    ]),


                // Timestamps (lecture seule en édition)
                Section::make('Informations système')
                    ->icon('heroicon-o-clock')
                    ->columnSpanFull()
                    ->compact()
                    ->visible(fn(string $context): bool => $context === 'edit')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                // CORRECTION ICI - Utilisation de Carbon pour formater les dates
                                TextInput::make('created_at')
                                    ->label('Créé le')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($state) {
                                        if (!$state) return '-';
                                        // Si c'est déjà une string, on la retourne telle quelle
                                        if (is_string($state)) return $state;
                                        // Si c'est un objet Carbon, on le formate
                                        if ($state instanceof Carbon) {
                                            return $state->format('d/m/Y H:i');
                                        }
                                        // Tentative de conversion
                                        try {
                                            return Carbon::parse($state)->format('d/m/Y H:i');
                                        } catch (\Exception $e) {
                                            return (string) $state;
                                        }
                                    }),

                                TextInput::make('updated_at')
                                    ->label('Mis à jour le')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(function ($state) {
                                        if (!$state) return '-';
                                        if (is_string($state)) return $state;
                                        if ($state instanceof Carbon) {
                                            return $state->format('d/m/Y H:i');
                                        }
                                        try {
                                            return Carbon::parse($state)->format('d/m/Y H:i');
                                        } catch (\Exception $e) {
                                            return (string) $state;
                                        }
                                    }),

                                TextInput::make('remember_token')
                                    ->label('Token de session')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn($state) => $state ? substr($state, 0, 20) . '...' : 'Non disponible'),
                            ]),
                    ]),
            ]);
    }
}
