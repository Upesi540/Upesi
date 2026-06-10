<?php

namespace App\Filament\App\Resources\TraderUsers;

use App\Filament\App\Resources\TraderUsers\Pages\CreateTraderUser;
use App\Filament\App\Resources\TraderUsers\Pages\EditTraderUser;
use App\Filament\App\Resources\TraderUsers\Pages\ListTraderUsers;
use App\Filament\App\Resources\TraderUsers\Pages\ViewTraderUser;
use App\Filament\App\Resources\TraderUsers\RelationManagers\TraderUsersMerchantProfileRelationManager;
use App\Filament\App\Resources\TraderUsers\RelationManagers\TraderUserWalletsRelationManager;
use App\Filament\App\Resources\TraderUsers\Schemas\TraderUserForm;
use App\Filament\App\Resources\TraderUsers\Tables\TraderUsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TraderUsersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;
    protected static string|UnitEnum|null $navigationGroup = 'Comptes & Profils';
    protected static ?string $navigationLabel = 'Utilisateurs';
    protected static ?string $modelLabel = 'Utilisateur';
    protected static ?string $pluralModelLabel = 'Utilisateurs';
    protected static ?string $recordTitleAttribute = 'email';

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

    /**
     * 🔥 FILTRE : Le trader ne voit que les utilisateurs QU'IL A CRÉÉS
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
                ->where('created_by', $user->id)
                ->whereDoesntHave('merchantProfiles', function ($q) {
                    $q->where('type', 'trader');
                });
        }

        return parent::getEloquentQuery();
    }

    public static function form(Schema $schema): Schema
    {
        return TraderUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TraderUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TraderUserWalletsRelationManager::class,
            TraderUsersMerchantProfileRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTraderUsers::route('/'),
            'create' => CreateTraderUser::route('/create'),
            'view' => ViewTraderUser::route('/{record}'),
            'edit' => EditTraderUser::route('/{record}/edit'),
        ];
    }
}
