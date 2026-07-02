<?php
// app/Services/Payment/Gateways/FedaPayGateway.php

namespace App\Services\Payment\Gateways;

use App\Models\Transaction;
use App\Services\Payment\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DTOs\DepositRequest;
use App\Services\Payment\DTOs\DepositResponse;
use App\Services\Payment\DTOs\WithdrawalRequest;
use App\Services\Payment\DTOs\WithdrawalResponse;
use App\Services\Payment\DTOs\WebhookRequest;
use App\Services\Payment\DTOs\WebhookResponse;
use App\Services\Payment\Services\CommissionService;
use App\Services\WalletService;
use FedaPay\FedaPay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FedaPayGateway implements PaymentGatewayInterface
{
    protected CommissionService $commissionService;
    protected WalletService $walletService;

    public function __construct(
        CommissionService $commissionService,
        WalletService $walletService
    ) {
        $this->commissionService = $commissionService;
        $this->walletService = $walletService;

        try {
            FedaPay::setApiKey(config('payment.gateways.fedapay.secret_key'));
            FedaPay::setEnvironment(config('payment.gateways.fedapay.environment'));
        } catch (\Exception $e) {
            Log::error('[FedaPay] Erreur initialisation configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getName(): string
    {
        return 'fedapay';
    }

    public function getSupportedCurrencies(): array
    {
        return ['XOF'];
    }

    public function supportsWithdrawals(): bool
    {
        return true;
    }

    public function initiateDeposit(DepositRequest $request): DepositResponse
    {
        $startTime = microtime(true);
        Log::info('[FedaPay] Initiation dépôt', [
            'user_id' => $request->user->id,
            'amount' => $request->amount,
            'currency' => $request->currency
        ]);

        try {
            return DB::transaction(function () use ($request, $startTime) {
                // 1. Trouver la devise
                $currency = \App\Models\Currency::where('code', $request->currency)->first();
                if (!$currency) {
                    Log::warning('[FedaPay] Devise non trouvée', ['currency' => $request->currency]);
                    throw new \Exception("Devise {$request->currency} non trouvée");
                }

                // 2. Calculer les frais
                $fee = $this->commissionService->calculateDepositFee(
                    $request->amount,
                    $this->getName()
                );

                // 3. Créer la transaction locale
                $transaction = Transaction::create([
                    'user_id' => $request->user->id,
                    'wallet_id' => $request->user->wallet?->id,
                    'currency_id' => $currency->id,
                    'amount' => $request->amount,
                    'fees' => $fee,
                    'type' => 'credit',
                    'operation' => 'deposit',
                    'status' => 'pending',
                    'gateway' => $this->getName(),
                    'reference' => 'DEP-' . uniqid() . '-' . date('Ymd'),
                    'metadata' => $request->metadata,
                    'tid' => null,
                ]);

                Log::debug('[FedaPay] Transaction locale créée', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference
                ]);
                $origin = str_contains(request()->url(), '/admin') ? 'admin' : 'app';                // 4. Appel FedaPay
                try {
                    $fedapayTransaction = \FedaPay\Transaction::create([
                        'description' => 'Dépôt via ' . $this->getName(),
                        'amount' => $request->amount,
                        'currency' => ['iso' => $this->convertToFedaPayCurrency($request->currency)],
                        'callback_url' => route('payment.callback', [
                            'gateway' => $this->getName(),
                            'origin' => $origin // On passe l'origine ici !
                        ]),
                        'customer' => [
                            'firstname' => $request->user->first_name ?? 'Client',
                            'lastname' => $request->user->last_name ?? '',
                            'email' => $request->user->email,
                            'phone_number' => [
                                'number' => $request->metadata['phone'],
                                'country' => $request->metadata['country'] ?? 'TG'
                            ]
                        ]
                    ]);

                    $token = $fedapayTransaction->generateToken();

                    Log::info('[FedaPay] Transaction FedaPay créée', [
                        'fedapay_id' => $fedapayTransaction->id,
                        'token_url' => $token->url
                    ]);
                } catch (\Exception $e) {
                    Log::error('[FedaPay] Échec création transaction FedaPay', [
                        'user_id' => $request->user->id,
                        'amount' => $request->amount,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

                // 5. Mettre à jour la transaction
                $transaction->update([
                    'tid' => $fedapayTransaction->id,
                    'token' => $token->url,
                    'provider_response' => json_decode(json_encode($fedapayTransaction), true),
                ]);

                Log::info('[FedaPay] Dépôt initié avec succès', [
                    'transaction_id' => $transaction->id,
                    'fedapay_id' => $fedapayTransaction->id,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000)
                ]);

                return new DepositResponse(
                    success: true,
                    transactionId: $transaction->id,
                    paymentUrl: $token->url,
                    status: 'pending',
                    reference: $transaction->reference
                );
            });
        } catch (\Exception $e) {
            Log::error('[FedaPay] Initiation dépôt échouée', [
                'user_id' => $request->user->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function initiateWithdrawal(WithdrawalRequest $request): WithdrawalResponse
    {
        $startTime = microtime(true);
        Log::info('[FedaPay] Initiation retrait', [
            'user_id' => $request->user->id,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'destination_type' => $request->destinationType
        ]);

        try {
            return DB::transaction(function () use ($request, $startTime) {
                if (!in_array($request->currency, $this->getSupportedCurrencies())) {
                    Log::warning('[FedaPay] Devise non supportée pour retrait', [
                        'currency' => $request->currency
                    ]);
                    throw new \Exception("Devise {$request->currency} non supportée");
                }

                $currency = \App\Models\Currency::where('code', $request->currency)->first();
                $feeDetails = $this->commissionService->calculateWithdrawalFee(
                    $request->amount,
                    $request->destinationType
                );

                $wallet = $request->user->getWalletByCurrency($request->currency);
                $totalToDebit = $request->amount + $feeDetails['total_fee'];

                if (!$wallet->hasSufficientBalance($totalToDebit)) {
                    Log::warning('[FedaPay] Solde insuffisant pour retrait', [
                        'user_id' => $request->user->id,
                        'balance' => $wallet->balance,
                        'required' => $totalToDebit
                    ]);
                    throw new \Exception("Solde insuffisant");
                }

                // Créer transaction
                $transaction = Transaction::create([
                    'user_id' => $request->user->id,
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                    'amount' => $request->amount,
                    'fees' => $feeDetails['total_fee'],
                    'fee_breakdown' => $feeDetails,
                    'type' => 'debit',
                    'operation' => 'withdrawal',
                    'status' => 'pending',
                    'gateway' => $this->getName(),
                    'mode' => $request->destinationType,
                    'reference' => 'WTH-' . uniqid() . '-' . date('Ymd'),
                    'metadata' => array_merge($request->metadata, [
                        'destination' => $request->destinationDetails
                    ]),
                    'tid' => null,
                ]);

                Log::debug('[FedaPay] Transaction locale créée pour retrait', [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->reference
                ]);

                // Appel FedaPay Payout
                try {
                    $payout = \FedaPay\Payout::create([
                        'amount' => $request->amount,
                        'currency' => ['iso' => $this->convertToFedaPayCurrency($request->currency)],
                        'mode' => $this->getPayoutMode($request->destinationType, $request->destinationDetails),
                        'description' => "Retrait - {$request->user->id}",
                        'customer' => [
                            'firstname' => $request->user->first_name,
                            'lastname' => $request->user->last_name,
                            'email' => $request->user->email,
                            'phone_number' => [
                                'number' => $request->destinationDetails['phone'] ?? $request->user->phone,
                                'country' => $request->destinationDetails['country_code'] ?? 'TG'
                            ]
                        ],
                        'merchant_reference' => $transaction->reference
                    ]);

                    $payout->send();

                    Log::info('[FedaPay] Payout créé', [
                        'payout_id' => $payout->id,
                        'amount' => $request->amount
                    ]);
                } catch (\Exception $e) {
                    Log::error('[FedaPay] Échec création payout', [
                        'user_id' => $request->user->id,
                        'amount' => $request->amount,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

                // Débiter le wallet
                $walletTransaction = $wallet->modifyBalance(
                    amount: $totalToDebit,
                    type: 'debit',
                    opType: 'withdrawal',
                    desc: "Retrait vers {$request->destinationType}",
                    source: $transaction
                );

                // Mettre à jour la transaction
                $transaction->update([
                    'tid' => $payout->id,
                    'wallet_transaction_id' => $walletTransaction->id,
                    'status' => 'processing',
                    'processed_at' => now(),
                    'provider_response' => json_decode(json_encode($payout), true),
                ]);

                if ($feeDetails['total_fee'] > 0) {
                    $this->commissionService->distributeCommission($transaction, $feeDetails['total_fee']);
                }

                Log::info('[FedaPay] Retrait initié avec succès', [
                    'transaction_id' => $transaction->id,
                    'payout_id' => $payout->id,
                    'duration_ms' => round((microtime(true) - $startTime) * 1000)
                ]);

                return new WithdrawalResponse(
                    success: true,
                    withdrawalId: $transaction->id,
                    status: 'processing',
                    message: "Retrait initié avec succès"
                );
            });
        } catch (\Exception $e) {
            Log::error('[FedaPay] Initiation retrait échouée', [
                'user_id' => $request->user->id,
                'amount' => $request->amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function handleWebhook(WebhookRequest $request): WebhookResponse
    {
        // 1. Récupération du JSON brut intact
        $payload = $request->rawContent;

        // 2. Extraction de la string propre depuis le tableau de headers de Symfony
        // Symfony store les headers sous forme de tableaux : ['valeur1', 'valeur2']
        $sigHeader = $request->headers['x-fedapay-signature'][0]
            ?? $request->headers['x-fedapay-signature']
            ?? '';

        // 3. Récupération du secret whsec_
        $secret = config('payment.gateways.fedapay.webhook_secret', env('FEDAPAY_WEBHOOK_SECRET'));

        Log::info('[FedaPay] Webhook reçu', [
            'payload' => $request->payload,
            'sig_header' => $sigHeader,
            'secret_prefix' => substr($secret, 0, 6) . '...'
        ]);

        try {
            // Le SDK reçoit bien sa string propre, son JSON brut et son secret
            \FedaPay\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::warning('[FedaPay] Signature invalide', [
                'error' => $e->getMessage(),
                'received_signature' => $sigHeader,
                'secret_prefix' => substr($secret, 0, 6) . '...'
            ]);
            return new WebhookResponse(false, 'invalid_signature', 'Signature verification failed');
        }

        Log::info('[FedaPay] Webhook reçu et signature validée !', ['payload' => $request->payload]);

        try {
            return DB::transaction(function () use ($request) {
                $data = $request->payload;

                // 💥 CORRECTION : Recherche de l'ID d'abord dans l'objet 'entity' de FedaPay
                $id = $data['entity']['id'] ?? $data['id'] ?? ($data['data']['id'] ?? null);

                if (!$id) {
                    Log::warning('[FedaPay] Webhook sans ID', ['payload' => $data]);
                    return new WebhookResponse(false, 'failed', 'ID manquant');
                }

                $transaction = Transaction::where('tid', $id)->first();

                if (!$transaction) {
                    Log::warning('[FedaPay] Webhook pour transaction inconnue', ['tid' => $id]);
                    return new WebhookResponse(false, 'not_found');
                }

                // 🔥 Ajout de 'cancelled' pour éviter les doublons
                if (in_array($transaction->status, ['approved', 'completed', 'failed', 'cancelled'])) {
                    Log::info('[FedaPay] Webhook déjà traité', [
                        'tid' => $id,
                        'status' => $transaction->status
                    ]);
                    return new WebhookResponse(true, 'already_processed');
                }

                // 💥 CORRECTION : Recherche du statut d'abord dans l'objet 'entity' de FedaPay
                $status = $data['entity']['status'] ?? $data['status'] ?? ($data['data']['status'] ?? 'unknown');

                Log::info('[FedaPay] Traitement webhook', [
                    'tid' => $id,
                    'operation' => $transaction->operation,
                    'fedapay_status' => $status,
                    'current_status' => $transaction->status
                ]);

                // ================================================
                // CAS 1: DÉPÔT RÉUSSI
                // ================================================
                if ($transaction->operation === 'deposit' && in_array($status, ['approved', 'transferred'])) {
                    try {
                        $netAmount = $transaction->amount - $transaction->fees;

                        $walletTransaction = $this->walletService->deposit(
                            $transaction->wallet,
                            $netAmount,
                            $transaction->fees,
                            $transaction->reference,
                            $transaction
                        );

                        if ($transaction->fees > 0) {
                            $commissionWallet = \App\Models\Wallet::getSystemCommission($transaction->currency_id);
                            if ($commissionWallet) {
                                $commissionWallet->modifyBalance(
                                    amount: $transaction->fees,
                                    type: 'credit',
                                    opType: 'commission',
                                    desc: "Commission dépôt #{$transaction->reference}",
                                    source: $transaction
                                );
                            }
                        }

                        $transaction->update([
                            'status' => 'completed',
                            'wallet_transaction_id' => $walletTransaction->id,
                            'processed_at' => now(),
                            'provider_response' => $data
                        ]);

                        Log::info('[FedaPay] Dépôt complété', [
                            'user_id' => $transaction->user_id,
                            'gross' => $transaction->amount,
                            'fee' => $transaction->fees,
                            'net' => $netAmount
                        ]);
                    } catch (\Exception $e) {
                        Log::error('[FedaPay] Erreur lors du crédit du wallet', [
                            'tid' => $id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                }

                // ================================================
                // CAS 2: DÉPÔT ANNULÉ ou REFUSÉ
                // ================================================
                elseif ($transaction->operation === 'deposit' && in_array($status, ['canceled', 'cancelled', 'declined', 'failed', 'expired'])) {
                    // ✅ Pour un dépôt annulé, rien à rembourser car l'argent n'a pas été débité
                    $transaction->update([
                        'status' => 'cancelled',
                        'provider_response' => $data,
                        'error_message' => "Dépôt {$status} par FedaPay",
                        'processed_at' => now()
                    ]);

                    Log::warning('[FedaPay] Dépôt annulé/refusé', [
                        'tid' => $id,
                        'status' => $status,
                        'user_id' => $transaction->user_id,
                        'amount' => $transaction->amount
                    ]);
                }

                // ================================================
                // CAS 3: RETRAIT RÉUSSI
                // ================================================
                elseif ($transaction->operation === 'withdrawal' && in_array($status, ['sent', 'paid'])) {
                    $transaction->update([
                        'status' => 'completed',
                        'processed_at' => now(),
                        'provider_response' => $data
                    ]);
                    Log::info('[FedaPay] Retrait complété', ['tid' => $id]);
                }

                // ================================================
                // CAS 4: RETRAIT ANNULÉ ou REFUSÉ
                // ================================================
                elseif ($transaction->operation === 'withdrawal' && in_array($status, ['canceled', 'cancelled', 'declined', 'failed', 'expired'])) {
                    // ✅ Pour un retrait annulé, on rembourse le wallet
                    if ($transaction->status === 'processing' || $transaction->status === 'pending') {
                        $wallet = $transaction->wallet;
                        if ($wallet) {
                            $wallet->modifyBalance(
                                amount: $transaction->amount + $transaction->fees,
                                type: 'credit',
                                opType: 'refund',
                                desc: "Remboursement retrait annulé #{$transaction->reference}",
                                source: $transaction
                            );
                        }
                    }

                    $transaction->update([
                        'status' => 'cancelled',
                        'provider_response' => $data,
                        'error_message' => "Retrait {$status} par FedaPay",
                        'processed_at' => now()
                    ]);

                    Log::warning('[FedaPay] Retrait annulé/refusé', [
                        'tid' => $id,
                        'status' => $status,
                        'user_id' => $transaction->user_id,
                        'amount' => $transaction->amount
                    ]);
                }

                // ================================================
                // CAS 5: AUTRES STATUTS
                // ================================================
                else {
                    $transaction->update([
                        'status' => $status,
                        'provider_response' => $data
                    ]);
                    Log::info('[FedaPay] Statut intermédiaire', [
                        'tid' => $id,
                        'status' => $status
                    ]);
                }

                return new WebhookResponse(true, 'success');
            });
        } catch (\Exception $e) {
            Log::error('[FedaPay] Webhook échoué', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->payload
            ]);
            return new WebhookResponse(false, 'failed', $e->getMessage());
        }
    }

    public function checkStatus(string $transactionId): array
    {
        Log::debug('[FedaPay] Vérification statut', ['transaction_id' => $transactionId]);

        try {
            $fedapayTransaction = \FedaPay\Transaction::retrieve($transactionId);
            return [
                'status' => $fedapayTransaction->status,
                'amount' => $fedapayTransaction->amount,
                'mode' => $fedapayTransaction->mode
            ];
        } catch (\Exception $e) {
            Log::debug('[FedaPay] Transaction non trouvée, tentative payout', ['transaction_id' => $transactionId]);

            try {
                $payout = \FedaPay\Payout::retrieve($transactionId);
                return [
                    'status' => $payout->status,
                    'amount' => $payout->amount
                ];
            } catch (\Exception $e) {
                Log::error('[FedaPay] Erreur vérification statut', [
                    'transaction_id' => $transactionId,
                    'error' => $e->getMessage()
                ]);
                return ['status' => 'unknown'];
            }
        }
    }

    protected function convertToFedaPayCurrency(string $currency): string
    {
        return match ($currency) {
            'USD', 'EUR' => 'XOF',
            default => $currency
        };
    }

    protected function getPayoutMode(string $destinationType, array $details): string
    {
        return $details['provider'] ?? 'mtn_' . strtolower($details['country_code'] ?? 'tg');
    }
}
