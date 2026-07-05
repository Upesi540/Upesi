<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Wallet;
use App\Models\Currency;
use App\Models\WalletTransaction;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class UserWalletOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    public function getColumns(): int | array
    {
        try {
            $user = Auth::user();

            if ($user && $user->hasRole(['admin', 'super_admin'])) {
                return [
                    'md' => 4,
                    'xl' => 4,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans getColumns: ' . $e->getMessage());
        }

        return [
            'md' => 3,
            'xl' => 2,
        ];
    }

    protected function getStats(): array
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $wallet = $user->wallet;
            $stats = [];
            $currencyCode = config('app.base_currency', 'XOF');

            // 1. Wallet Personnel
            if ($wallet) {
                // Solde disponible
                $stats[] = Stat::make(
                    'Mon Solde Disponible',
                    number_format($wallet->available_balance, 0, '.', ' ') . ' ' . $currencyCode
                )
                ->description('Recharger le compte')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('success')
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'onclick' => "window.location.href='/app/my-wallet'",
                ]);

                // ⭐ NOUVEAU : Argent gelé (transactions en attente)
                $frozenAmount = $this->getFrozenAmount($wallet->id);
                if ($frozenAmount > 0) {
                    $stats[] = Stat::make(
                        '🔒 Argent Gelé',
                        number_format($frozenAmount, 0, '.', ' ') . ' ' . $currencyCode
                    )
                    ->description('Fonds en attente de validation')
                    ->descriptionIcon('heroicon-m-pause-circle')
                    ->color('warning')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'onclick' => "window.location.href='/app/my-wallet/transactions?type=freeze'",
                    ]);
                }

                // ⭐⭐ NOUVEAU : Argent qui va être libéré (commandes en cours)
                $pendingReleaseAmount = $this->getPendingReleaseAmount($wallet->id);
                if ($pendingReleaseAmount > 0) {
                    $stats[] = Stat::make(
                        '🔄 À Libérer',
                        number_format($pendingReleaseAmount, 0, '.', ' ') . ' ' . $currencyCode
                    )
                    ->description('Fonds à débloquer prochainement')
                    ->descriptionIcon('heroicon-m-arrow-trending-up')
                    ->color('info')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'onclick' => "window.location.href='/app/orders/pending'",
                    ]);
                }

                // ⭐⭐⭐ NOUVEAU : Commandes en cours
                $pendingOrdersCount = $this->getPendingOrdersCount($user->id);
                if ($pendingOrdersCount > 0) {
                    $stats[] = Stat::make(
                        '📦 Commandes en cours',
                        $pendingOrdersCount
                    )
                    ->description('Avec fonds gelés')
                    ->descriptionIcon('heroicon-m-shopping-bag')
                    ->color('gray')
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                        'onclick' => "window.location.href='/app/orders/pending'",
                    ]);
                }
            }

            // 2. Wallets Système (Admin & Super Admin)
            if ($user && $user->hasRole(['admin', 'super_admin'])) {
                $currency = Currency::where('code', $currencyCode)->first();

                if ($currency) {
                    // Commission Wallet
                    $commissionWallet = Wallet::where('holder_type', 'system_commission')
                        ->where('currency_id', $currency->id)
                        ->first();

                    if ($commissionWallet) {
                        $stats[] = Stat::make(
                            '💰 Commission Système',
                            number_format($commissionWallet->available_balance, 0, '.', ' ') . ' ' . $currencyCode
                        )
                        ->description('Commissions collectées')
                        ->descriptionIcon('heroicon-m-presentation-chart-line')
                        ->color('warning')
                        ->extraAttributes([
                            'class' => 'cursor-pointer',
                            'onclick' => "window.location.href='/app/system-wallets/commission'",
                        ]);
                    }

                    // Escrow Wallet
                    $escrowWallet = Wallet::where('holder_type', 'system_escrow')
                        ->where('currency_id', $currency->id)
                        ->first();

                    if ($escrowWallet) {
                        $stats[] = Stat::make(
                            '🔒 Séquestre Système',
                            number_format($escrowWallet->available_balance, 0, '.', ' ') . ' ' . $currencyCode
                        )
                        ->description('Fonds en attente de validation')
                        ->descriptionIcon('heroicon-m-shield-check')
                        ->color('info')
                        ->extraAttributes([
                            'class' => 'cursor-pointer',
                            'onclick' => "window.location.href='/app/system-wallets/escrow'",
                        ]);
                    }

                    // Total Système (Super Admin seulement)
                    if ($user->hasRole('super_admin')) {
                        $totalSystemBalance = Wallet::whereIn('holder_type', ['system_commission', 'system_escrow'])
                            ->where('currency_id', $currency->id)
                            ->sum('available_balance');

                        $stats[] = Stat::make(
                            '🏦 Total Système',
                            number_format($totalSystemBalance, 0, '.', ' ') . ' ' . $currencyCode
                        )
                        ->description('Total des fonds système')
                        ->descriptionIcon('heroicon-m-building-office')
                        ->color('gray');
                    }
                }
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('Erreur dans getStats: ' . $e->getMessage());

            // Retourner au moins le wallet personnel en cas d'erreur
            if (isset($user) && isset($user->wallet)) {
                return [
                    Stat::make('Mon Solde', '0 ' . config('app.base_currency', 'XOF'))
                        ->description('Erreur de chargement')
                        ->color('danger'),
                ];
            }

            return [];
        }
    }

    /**
     * Calcule le montant total gelé (transactions en attente)
     * Ne prend que les transactions de type 'freeze' avec status 'pending'
     */
    private function getFrozenAmount(string $walletId): float
    {
        try {
            return WalletTransaction::where('wallet_id', $walletId)
                ->where('type', 'freeze')
                ->where('status', 'pending')
                ->sum('amount');
        } catch (\Exception $e) {
            Log::error('Erreur dans getFrozenAmount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcule le montant total qui va être libéré
     * Prend les transactions 'freeze' en attente liées aux commandes
     */
    private function getPendingReleaseAmount(string $walletId): float
    {
        try {
            return WalletTransaction::where('wallet_id', $walletId)
                ->where('type', 'freeze')
                ->where('status', 'pending')
                ->whereHas('transactionable', function ($query) {
                    // Filtre sur les commandes en statut 'pending' ou 'shipped'
                    // (qui ne sont pas encore livrées)
                    $query->whereHasMorph('transactionable', [Order::class], function ($q) {
                        $q->whereIn('status', ['pending', 'confirmed', 'shipped']);
                    });
                })
                ->sum('amount');
        } catch (\Exception $e) {
            Log::error('Erreur dans getPendingReleaseAmount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compte le nombre de commandes en cours
     */
    private function getPendingOrdersCount(string $userId): int
    {
        try {
            return Order::where('buyer_id', $userId)
                ->whereIn('status', ['pending', 'confirmed', 'shipped'])
                ->where('payment_status', 'pending')
                ->count();
        } catch (\Exception $e) {
            Log::error('Erreur dans getPendingOrdersCount: ' . $e->getMessage());
            return 0;
        }
    }
}
