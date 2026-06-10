<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->deferLoading() // Charge la page instantanément, puis les données
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
                TextColumn::make('roles.display_name')
                    ->label('Rôle')
                    ->badge()
                    ->default('Utilisateur')
                    ->tooltip(fn($record) => $record->roles->pluck('description')->join(', '))
                    ->color('primary'),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                IconColumn::make('is_banned')
                    ->label('Banni')
                    ->boolean(),
            ])
            ->filters([

                TrashedFilter::make(),

            ])
            ->recordActions([
                Action::make('assign_roles')
                    ->label('Assigner Rôles')
                    ->icon('heroicon-o-shield-check')
                    ->color('warning')
                    // C'EST CETTE PARTIE QUI MANQUE :
                    ->fillForm(fn(User $record): array => [
                        'roles' => $record->roles->pluck('id')->toArray(),
                    ])
                    ->form([
                        Select::make('roles')
                            ->multiple()
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'display_name',
                                modifyQueryUsing: function (Builder $query) {
                                    // L'utilisateur connecté
                                    $user = Auth::user();
                                    // Si ce n'est pas un super_admin, on exclut le rôle super_admin
                                    if (!$user->hasRole('super_admin')) {
                                        $query->where('name', '!=', 'super_admin');
                                    }
                                    return $query;
                                }
                            )
                            ->preload()
                            ->label('Assigner les rôles')
                    ])
                    ->action(function (User $record, array $data): void {

                        // $record->roles()->sync($data['roles']);
                        Notification::make()
                            ->title('Rôles mis à jour')
                            ->success()
                            ->send();
                    }),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    // leaving the table.
                    DeleteAction::make(),
                    ForceDeleteAction::make(),
                    RestoreAction::make(),
                ])

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
