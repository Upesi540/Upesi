<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        // 1. On commence par l'onglet "Tous"
        $tabs = [
            'all' => Tab::make('Tous les utilisateurs')
                ->badge(User::count()),
        ];

        // 2. On récupère tous les rôles en base de données
        $roles = Role::all();

        // 3. On génère dynamiquement un onglet par rôle
        foreach ($roles as $role) {
            $tabs[$role->name] = Tab::make($role->display_name) // Ou $role->label si tu as un champ label
                ->modifyQueryUsing(fn($query) => $query->role($role->name))
                ->badge(User::role($role->name)->count());
        }

        return $tabs;
    }
}
