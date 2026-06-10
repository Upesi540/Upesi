<?php

namespace App\Filament\App\Resources\TraderUsers\Pages;

use App\Filament\App\Resources\TraderUsers\TraderUsersResource;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListTraderUsers extends ListRecords
{
    protected static string $resource = TraderUsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $user = auth()->user();


        // Pour un trader, on ne montre que les types qu'il peut créer
            return [
                'all' => Tab::make('Tous mes utilisateurs')
                    ->badge(User::where('created_by', $user->id)->count()),

                'producer' => Tab::make('Producteurs')
                    ->modifyQueryUsing(fn($query) => $query
                        ->where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'producer'))
                    )
                    ->badge(User::where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'producer'))
                        ->count()
                    ),

                'supplier' => Tab::make('Fournisseurs')
                    ->modifyQueryUsing(fn($query) => $query
                        ->where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'supplier'))
                    )
                    ->badge(User::where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'supplier'))
                        ->count()
                    ),

                'provider' => Tab::make('Prestataires')
                    ->modifyQueryUsing(fn($query) => $query
                        ->where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'provider'))
                    )
                    ->badge(User::where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'provider'))
                        ->count()
                    ),

                'transporter' => Tab::make('Transporteurs')
                    ->modifyQueryUsing(fn($query) => $query
                        ->where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'transporter'))
                    )
                    ->badge(User::where('created_by', $user->id)
                        ->whereHas('merchantProfiles', fn($q) => $q->where('type', 'transporter'))
                        ->count()
                    ),

                'customer' => Tab::make('Clients')
                    ->modifyQueryUsing(fn($query) => $query
                        ->where('created_by', $user->id)
                        ->whereDoesntHave('merchantProfiles') // Les clients n'ont pas de merchant profile
                    )
                    ->badge(User::where('created_by', $user->id)
                        ->whereDoesntHave('merchantProfiles')
                        ->count()
                    ),
            ];



    }
}
