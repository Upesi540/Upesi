<?php
// app/Services/Payment/Services/PaymentGatewayManager.php

namespace App\Services\Payment\Services;

use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\Gateways\FedaPayGateway;

use App\Services\WalletService;
use InvalidArgumentException;

class PaymentGatewayManager
{
    protected array $gateways = [];

    protected CommissionService $commissionService;
    protected WalletService $walletService;

    public function __construct(
        CommissionService $commissionService,
        WalletService $walletService
    ) {
        $this->commissionService = $commissionService;
        $this->walletService = $walletService;

        // Enregistrer les gateways disponibles
        $this->registerGateways();
    }

    protected function registerGateways(): void
    {
        $this->gateways = [
            'fedapay' => new FedaPayGateway($this->commissionService, $this->walletService),
            // 'stripe' => new StripeGateway($this->commissionService, $this->walletService),
            // 'paypal' => new PayPalGateway($this->commissionService, $this->walletService),
        ];
    }

    /**
     * Obtenir un gateway par son nom
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$name])) {
            throw new InvalidArgumentException("Gateway {$name} non supporté");
        }

        return $this->gateways[$name];
    }

    /**
     * Obtenir tous les gateways disponibles
     */
    public function getAvailableGateways(): array
    {
        return $this->gateways;
    }

    /**
     * Obtenir les gateways qui supportent les retraits
     */
    public function getGatewaysSupportingWithdrawals(): array
    {
        return array_filter($this->gateways, function ($gateway) {
            return $gateway->supportsWithdrawals();
        });
    }

    /**
     * Obtenir les devises supportées par un gateway
     */
    public function getSupportedCurrencies(string $gatewayName): array
    {
        return $this->getGateway($gatewayName)->getSupportedCurrencies();
    }
}
