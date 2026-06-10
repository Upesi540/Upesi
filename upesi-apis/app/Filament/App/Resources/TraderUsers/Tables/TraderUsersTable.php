<?php

namespace App\Filament\App\Resources\TraderUsers\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TraderUsersTable
{
    public static function configure(Table $table): Table
    {

        return $table
            ->deferLoading()
            ->columns([
                ImageColumn::make('profile_photo_path')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        $name = trim($record->first_name . ' ' . $record->last_name);
                        $initials = collect(explode(' ', $name))
                            ->map(fn($segment) => strtoupper(substr($segment, 0, 1)))
                            ->take(2)
                            ->join('');

                        return 'https://ui-avatars.com/api/?name=' . urlencode($initials) . '&color=FFFFFF&background=2563eb&bold=true&size=100&length=2';
                    })
                    ->size(40)
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                IconColumn::make('email_verified_at')
                    ->label('Vérifié')
                    ->boolean()
                    ->getStateUsing(fn($record) => !is_null($record->email_verified_at))
                    ->tooltip(fn($record) => $record->email_verified_at ? "Vérifié le {$record->email_verified_at->format('d/m/Y H:i')}" : 'Non vérifié'),

                // 🔥 Affichage du type merchant (producteur, fournisseur, etc.) au lieu du rôle Spatie
                TextColumn::make('merchant_type')
                    ->label('Type de compte')
                    ->getStateUsing(fn($record) => optional($record->merchantProfiles->first())->type)
                    ->formatStateUsing(fn($state) => match($state) {
                        'producer' => '🌾 Producteur',
                        'supplier' => '📦 Fournisseur',
                        'provider' => '🛠️ Prestataire',
                        'transporter' => '🚚 Transporteur',
                        'customer' => '👤 Client',
                        default => '📝 En attente',
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'producer' => 'success',
                        'supplier' => 'info',
                        'provider' => 'warning',
                        'transporter' => 'primary',
                        'customer' => 'gray',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(fn($record) =>
                        !$record->is_active ? 'suspendu' :
                        ($record->is_approved ? 'approuvé' : 'en_attente')
                    )
                    ->formatStateUsing(fn($state) => match($state) {
                        'approuvé' => '✅ Approuvé',
                        'en_attente' => '⏳ En attente',
                        'suspendu' => '⚠️ Suspendu',
                        default => '❓ Inconnu',
                    })
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'approuvé' => 'success',
                        'en_attente' => 'warning',
                        'suspendu' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->tooltip(fn($record) => $record->created_at->format('H:i:s')),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                // ✅ Action : Voir (lecture seule)
                ViewAction::make()
                    ->label('Voir')
                    ->icon('heroicon-o-eye'),
                EditAction::make()
                    ->label('Modifier')
                    ->icon('heroicon-o-pencil')

                // // ✅ Action : Réinviter
                // Action::make('resend_invitation')
                //     ->label('Réinviter')
                //     ->icon('heroicon-o-envelope')
                //     ->color('info')
                //     ->action(function($record) {
                //         // Envoyer une nouvelle invitation
                //         $record->sendEmailVerificationNotification();

                //         Notification::make()
                //             ->title('Invitation envoyée')
                //             ->body('Un email d\'invitation a été renvoyé à ' . $record->email)
                //             ->success()
                //             ->send();
                //     })
                //     ->visible(fn($record) => !$record->hasVerifiedEmail() && !$record->is_approved),

                // ⚠️ Action : Signaler un problème (au lieu de modifier)

            ])
            ->bulkActions([
                // ❌ PAS de suppression groupée pour le trader
            ])
            ->recordUrl(null) // Pas de lien vers l'édition
            ->defaultSort('created_at', 'desc');
    }
}
