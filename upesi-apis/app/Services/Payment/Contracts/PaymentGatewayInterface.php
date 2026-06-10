<?php
// app/Services/Payment/Contracts/PaymentGatewayInterface.php

namespace App\Services\Payment\Contracts;

use App\Models\User;
use App\Services\Payment\DTOs\DepositRequest;
use App\Services\Payment\DTOs\DepositResponse;
use App\Services\Payment\DTOs\WithdrawalRequest;
use App\Services\Payment\DTOs\WithdrawalResponse;
use App\Services\Payment\DTOs\WebhookRequest;
use App\Services\Payment\DTOs\WebhookResponse;

interface PaymentGatewayInterface
{
    /**
     * Initier un dépôt (recharge)
     */
    public function initiateDeposit(DepositRequest $request): DepositResponse;

    /**
     * Initier un retrait (payout)
     */
    public function initiateWithdrawal(WithdrawalRequest $request): WithdrawalResponse;

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkStatus(string $transactionId): array;

    /**
     * Traiter un webhook
     */
    public function handleWebhook(WebhookRequest $request): WebhookResponse;

    /**
     * Obtenir le nom du gateway
     */
    public function getName(): string;

    /**
     * Obtenir les devises supportées
     */
    public function getSupportedCurrencies(): array;

    /**
     * Vérifier si le gateway supporte les retraits
     */
    public function supportsWithdrawals(): bool;
}
