<?php

// namespace App\Filament\Resources\Users\Schemas;

// use Filament\Actions\Action;
// use Filament\Infolists\Components\IconEntry;
// use Filament\Infolists\Components\ImageEntry;
// use Filament\Infolists\Components\TextEntry;
// use Filament\Schemas\Components\Fieldset;
// use Filament\Schemas\Components\Grid;
// use Filament\Schemas\Components\Group;
// use Filament\Schemas\Components\Section;
// use Filament\Schemas\Schema;

// class TraderUserInfolist
// {
//     public static function configure(Schema $schema): Schema
//     {
//         return $schema
//             ->columns(3)
//             ->components([
//                 // Avatar - colonne de gauche
//                 Section::make('Photo de profil')
//                     ->icon('heroicon-o-camera')
//                     ->columnSpan(1)
//                     ->schema([
//                         ImageEntry::make('profile_photo_path')
//                             ->label('')
//                             ->circular()
//                             ->extraAttributes(['class' => 'flex justify-center cursor-pointer shadow-lg'])
//                             // On définit l'action au clic
//                             ->action(
//                                 Action::make('zoom')
//                                     ->modalHeading('Photo de profil')
//                                     ->modalContent(fn($record) => view('filament.components.image-zoom', [
//                                         'url' => $record->profile_photo_path
//                                             ? asset('storage/' . $record->profile_photo_path)
//                                             : 'https://ui-avatars.com/api/?name=' . urlencode($record->first_name) . '&background=2563eb&color=fff&size=512'
//                                     ]))
//                                     ->modalSubmitAction(false) // On cache les boutons de la modale
//                                     ->modalCancelAction(false)
//                             )
//                             ->defaultImageUrl(function ($record) {
//                                 $name = trim($record->first_name . ' ' . $record->last_name);
//                                 $initials = collect(explode(' ', $name))
//                                     ->map(fn($segment) => strtoupper(substr($segment, 0, 1)))
//                                     ->take(2)
//                                     ->join('');

//                                 return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&color=FFFFFF&background=2563eb&bold=true&size=200&length=2';
//                             })
//                             ->imageSize(200)
//                             ->extraAttributes(['class' => 'flex justify-center']),

//                         TextEntry::make('profile_photo_path')
//                             ->label('Chemin')
//                             ->badge()
//                             ->color('gray')
//                             ->copyable()
//                             ->copyMessage('Chemin copié !')
//                             ->visible(fn($record) => !empty($record->profile_photo_path))
//                             ->columnSpanFull(),
//                     ]),

//                 // Section Identité - Informations personnelles (2 colonnes)
//                 Section::make('Identité')
//                     ->description('Informations personnelles de l\'utilisateur')
//                     ->icon('heroicon-o-user')
//                     ->columnSpan(2)
//                     ->schema([
//                         Grid::make(2)
//                             ->schema([
//                                 TextEntry::make('first_name')
//                                     ->label('Prénom')
//                                     ->placeholder('Non renseigné')
//                                     ->weight('bold'),

//                                 TextEntry::make('last_name')
//                                     ->label('Nom')
//                                     ->placeholder('Non renseigné')
//                                     ->weight('bold'),

//                                 TextEntry::make('name')
//                                     ->label('Nom d\'utilisateur')
//                                     ->placeholder('Non renseigné')
//                                     ->badge()
//                                     ->color('gray'),
//                                 TextEntry::make('country.name')
//                                     ->label('Pays')
//                                     ->placeholder('Non renseigné')
//                                     ->badge()
//                                     ->color('gray'),

//                                 TextEntry::make('email')
//                                     ->label('Adresse email')
//                                     ->icon('heroicon-o-envelope')
//                                     ->placeholder('Non renseigné')
//                                     ->copyable()
//                                     ->copyMessage('Email copié !')
//                                     ->color('primary'),

//                                 TextEntry::make('phone')
//                                     ->label('Téléphone')
//                                     ->icon('heroicon-o-phone')
//                                     ->placeholder('Non renseigné')
//                                     ->copyable()
//                                     ->copyMessage('Téléphone copié !')
//                                     ->badge()
//                                     ->color('success'),

//                                 TextEntry::make('preferredCurrency.code')
//                                     ->label('Devise préférée ')
//                                     ->icon('heroicon-o-phone')
//                                     ->placeholder('Non renseigné')
//                                     ->copyable()
//                                     ->badge()
//                                     ->color('success'),
//                                 TextEntry::make('email_verified_at')
//                                     ->label('Vérification email')
//                                     ->dateTime('d/m/Y H:i')
//                                     ->icon('heroicon-o-check-badge')
//                                     ->placeholder('Non vérifié')
//                                     ->badge()
//                                     ->color(fn($state) => $state ? 'success' : 'warning'),
//                             ]),
//                     ]),

//                 // Section Statuts et permissions (sur la ligne suivante)
//                 Section::make('Statuts & Permissions')
//                     ->description('État du compte et autorisations')
//                     ->icon('heroicon-o-shield-check')
//                     ->columnSpan(1)
//                     ->schema([
//                         Fieldset::make('Statuts du compte')
//                             ->schema([
//                                 IconEntry::make('is_active')
//                                     ->label('Actif')
//                                     ->boolean()
//                                     ->trueIcon('heroicon-o-check-circle')
//                                     ->falseIcon('heroicon-o-x-circle')
//                                     ->trueColor('success')
//                                     ->falseColor('danger')
//                                     ->tooltip('Compte actif ou bloqué'),

//                                 IconEntry::make('is_approved')
//                                     ->label('Approuvé')
//                                     ->boolean()
//                                     ->trueIcon('heroicon-o-check-badge')
//                                     ->falseIcon('heroicon-o-clock')
//                                     ->trueColor('success')
//                                     ->falseColor('warning')
//                                     ->tooltip('Validation administrative'),

//                                 IconEntry::make('is_banned')
//                                     ->label('Banni')
//                                     ->boolean()
//                                     ->trueIcon('heroicon-o-no-symbol')
//                                     ->falseIcon('heroicon-o-check')
//                                     ->trueColor('danger')
//                                     ->falseColor('success')
//                                     ->tooltip('Bannissement actif'),

//                                 IconEntry::make('has_email_authentication')
//                                     ->label('Auth email')
//                                     ->boolean()
//                                     ->trueIcon('heroicon-o-lock-closed')
//                                     ->falseIcon('heroicon-o-lock-open')
//                                     ->trueColor('success')
//                                     ->falseColor('gray')
//                                     ->tooltip('Authentification par email activée'),

//                                 TextEntry::make('current_team_id')
//                                     ->label('Équipe')
//                                     ->badge()
//                                     ->color('info')
//                                     ->formatStateUsing(fn($state) => $state ? "Équipe #{$state}" : 'Aucune')
//                                     ->placeholder('Aucune équipe'),
//                             ]),
//                     ]),

//                 // Section Dates et chronologie
//                 Section::make('Chronologie')
//                     ->description('Dates importantes du compte')
//                     ->icon('heroicon-o-calendar')
//                     ->columnSpan(1)
//                     ->schema([
//                         Grid::make(1)
//                             ->schema([
//                                 TextEntry::make('created_at')
//                                     ->label('Création')
//                                     ->dateTime('d/m/Y H:i')
//                                     ->icon('heroicon-o-calendar-days')
//                                     ->badge()
//                                     ->color('gray'),

//                                 TextEntry::make('updated_at')
//                                     ->label('Mise à jour')
//                                     ->dateTime('d/m/Y H:i')
//                                     ->icon('heroicon-o-calendar-days')
//                                     ->badge()
//                                     ->color('gray'),

//                                 TextEntry::make('id')
//                                     ->label('ID Utilisateur')
//                                     ->badge()
//                                     ->color('primary')
//                                     ->icon('heroicon-o-identification')
//                                     ->copyable()
//                                     ->copyMessage('ID copié !'),
//                             ]),
//                     ]),

//                 // Section Authentification 2 Facteurs
//                 Section::make('Authentification à deux facteurs (2FA)')
//                     ->description('Configuration de sécurité avancée')
//                     ->icon('heroicon-o-shield-exclamation')
//                     ->columnSpan(2)
//                     ->collapsed()
//                     ->schema([
//                         Grid::make(2)
//                             ->schema([
//                                 Group::make()
//                                     ->schema([
//                                         TextEntry::make('app_authentication_secret')
//                                             ->label('Secret 2FA')
//                                             ->placeholder('Non configuré')
//                                             ->limit(30)
//                                             ->copyable()
//                                             ->copyMessage('Secret copié !')
//                                             ->extraAttributes(['class' => 'font-mono text-xs'])
//                                             ->badge()
//                                             ->color(fn($state) => $state ? 'warning' : 'gray'),
//                                     ]),

//                                 Group::make()
//                                     ->schema([
//                                         TextEntry::make('app_authentication_recovery_codes')
//                                             ->label('Codes de récupération')
//                                             ->placeholder('Non générés')
//                                             ->formatStateUsing(function ($state) {
//                                                 if (!$state) return 'Aucun code disponible';
//                                                 $codes = json_decode($state, true);
//                                                 return count($codes) . ' code(s) de récupération';
//                                             })
//                                             ->badge()
//                                             ->color(fn($state) => $state ? 'warning' : 'gray')
//                                             ->copyable()
//                                             ->copyMessage('Codes copiés !'),
//                                     ]),
//                             ]),
//                     ]),

//                 // Section Suppression/Bannissement (Soft Delete)
//                 Section::make('Suppression & Bannissement')
//                     ->description('Historique des actions de modération')
//                     ->icon('heroicon-o-trash')
//                     ->columnSpanFull()
//                     ->collapsed()
//                     ->visible(fn($record) => $record->trashed() || $record->is_banned || $record->deletion_reason)
//                     ->schema([
//                         Grid::make(3)
//                             ->schema([
//                                 IconEntry::make('deleted_at')
//                                     ->label('Compte supprimé')
//                                     ->boolean()
//                                     ->trueIcon('heroicon-o-trash')
//                                     ->falseIcon('heroicon-o-check-circle')
//                                     ->trueColor('danger')
//                                     ->falseColor('success')
//                                     ->formatStateUsing(fn($state) => $state ? 'Supprimé' : 'Actif'),

//                                 TextEntry::make('deleted_at')
//                                     ->label('Date de suppression')
//                                     ->dateTime('d/m/Y H:i')
//                                     ->placeholder('Non supprimé')
//                                     ->visible(fn($record) => $record->trashed()),

//                                 TextEntry::make('deletion_reason')
//                                     ->label('Raison')
//                                     ->placeholder('Aucune raison fournie')
//                                     ->markdown()
//                                     ->columnSpan(2)
//                                     ->visible(fn($state) => !empty($state)),

//                                 TextEntry::make('deleted_by')
//                                     ->label('Supprimé par')
//                                     ->formatStateUsing(function ($state) {
//                                         if (!$state) return 'N/A';
//                                         $user = \App\Models\User::find($state);
//                                         return $user ? $user->name : 'Utilisateur inconnu';
//                                     })
//                                     ->badge()
//                                     ->color('danger')
//                                     ->visible(fn($record) => $record->deleted_by),
//                             ]),
//                     ]),

//                 // Section Remember Token (cachée par défaut, visible seulement si présent)
//                 TextEntry::make('remember_token')
//                     ->label('Token de session')
//                     ->placeholder('Non disponible')
//                     ->badge()
//                     ->color('gray')
//                     ->visible(fn($record) => !empty($record->remember_token))
//                     ->columnSpanFull(),
//             ]);
//     }
// }
