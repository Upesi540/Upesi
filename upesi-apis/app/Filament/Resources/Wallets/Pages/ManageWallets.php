<?php

namespace App\Filament\Resources\Wallets\Pages;

use App\Filament\Resources\Wallets\WalletResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageWallets extends ManageRecords
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Créer un portefeuille'),
        ];
    }

    /**
     * Organisation de la liste en onglets logiques
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Tous les comptes')
                ->icon('heroicon-m-list-bullet'),

            'users' => Tab::make('Clients')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('holder_type', 'user'))
                ->icon('heroicon-m-users'),

            'commissions' => Tab::make('Gains Upesi')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('holder_type', 'system_commission'))
                ->icon('heroicon-m-arrow-trending-up')
                ->badgeColor('success')
                // Optionnel : affiche le nombre de comptes de commission
                ->badge(\App\Models\Wallet::where('holder_type', 'system_commission')->count()),

            'escrow' => Tab::make('Séquestre')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('holder_type', 'system_escrow'))
                ->icon('heroicon-m-shield-check')
                ->badgeColor('warning'),
        ];
    }
}
