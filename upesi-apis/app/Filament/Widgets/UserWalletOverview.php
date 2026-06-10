<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class UserWalletOverview extends BaseWidget
{
    // On désactive le rafraîchissement automatique agressif
    protected ?string $pollingInterval = null;
   public function getColumns(): int | array
{
    return [
        'md' => 3,
        'xl' => 2,
    ];
}
    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) return [];

        return [
            Stat::make('Mon Solde Disponible', number_format($wallet->available_balance, 0, '.', ' ') . ' ' . config('app.base_currency'))
                ->description('Recharger le compte')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/app/my-wallet'",
                ]),
            Stat::make('Solde Gelé', number_format($wallet->frozen_balance, 0, '.', ' ') . ' ' . config('app.base_currency'))
                ->description('Paiements en attente')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('warning'),

        ];
    }
}
