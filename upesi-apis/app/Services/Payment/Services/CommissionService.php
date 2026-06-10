<?php
// app/Services/Payment/Services/CommissionService.php

namespace App\Services\Payment\Services;

use App\Models\Transaction;

class CommissionService
{
    // Configuration des commissions par type d'opération
    protected array $commissionRates = [
        'deposit' => [
            'fedapay' => 0.00,      // 0% (dépôt gratuit)
            // 'stripe' => 2.9,         // 2.9% + frais fixes
            // 'paypal' => 3.5,          // 3.5%
        ],
        'withdrawal' => [
            'fedapay' => 1.5,         // 1.5% pour retrait FedaPay
            // 'bank_transfer' => 2.0,   // 2% pour virement bancaire
        ],
        'marketplace' => [
            'sale' => 5.0,             // 5% commission sur vente
            'withdrawal_fixed' => 500,  // Frais fixe de retrait (optionnel)
            'trader_commission' => 2.0,          // 🔥 Commission du négociant (2%)

        ]
    ];

    /**
     * Calculer la commission pour un dépôt
     */
    public function calculateDepositFee(float $amount, string $gateway): float
    {
        $rate = $this->commissionRates['deposit'][$gateway] ?? 0;
        return ($amount * $rate) / 100;
    }

    /**
     * Calculer la commission pour un retrait
     */
    public function calculateWithdrawalFee(float $amount, string $method): array
    {
        $percentage = $this->commissionRates['withdrawal'][$method] ?? 1.5;
        $fixedFee = $this->commissionRates['marketplace']['withdrawal_fixed'] ?? 500;

        $feeAmount = ($amount * $percentage) / 100;
        $totalFee = $feeAmount + $fixedFee;

        return [
            'percentage' => $percentage,
            'percentage_amount' => $feeAmount,
            'fixed_fee' => $fixedFee,
            'total_fee' => $totalFee,
            'net_amount' => $amount - $totalFee
        ];
    }

    /**
     * Distribuer les commissions entre plateforme et partenaires
     */
    // app/Services/Payment/Services/CommissionService.php

    public function distributeCommission(Transaction $transaction, float $totalFee): array
    {
        // 🔥 100% pour la plateforme (pas de partenaire)
        $platformShare = $totalFee;  // ← TOUT pour toi
        $partnerShare = 0;

        // Créditer le wallet système (la plateforme)
        if ($platformShare > 0) {
            $commissionWallet = \App\Models\Wallet::getSystemCommission($transaction->currency_id);
            if ($commissionWallet) {
                $commissionWallet->modifyBalance(
                    amount: $platformShare,
                    type: 'credit',
                    opType: 'commission',
                    desc: "Commission retrait #{$transaction->reference}",
                    source: $transaction
                );
            }
        }

        return [
            'platform' => $platformShare,
            'partner' => $partnerShare
        ];
    }

    /**
     * Calculer la commission du négociant sur une vente groupée
     */
    public function calculateTraderCommission(float $totalAmount): float
    {
        $rate = $this->commissionRates['marketplace']['trader_commission'] ?? 2.0;
        return ($totalAmount * $rate) / 100;
    }
}
