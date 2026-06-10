<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use App\Services\Payment\DTOs\DepositRequest;
use App\Services\Payment\DTOs\WebhookRequest;
use App\Services\Payment\DTOs\WithdrawalRequest;
use App\Services\Payment\Services\PaymentGatewayManager;
use App\Traits\ResponseFormat;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use ResponseFormat;

    protected PaymentGatewayManager $gatewayManager;

    public function __construct(PaymentGatewayManager $gatewayManager)
    {
        $this->gatewayManager = $gatewayManager;
    }

    /**
     * Initier un dépôt
     */
    // public function deposit(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'gateway' => 'required|string|in:fedapay,stripe,paypal',
    //         'amount' => 'required|numeric|min:100',
    //         'currency' => 'required|string|size:3',
    //         'phone' => 'nullable|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->ResponseERROR('Validation échouée', $validator->errors());
    //     }

    //     try {
    //         $gateway = $this->gatewayManager->getGateway($request->gateway);

    //         // Vérifier que la devise est supportée
    //         if (!in_array($request->currency, $gateway->getSupportedCurrencies())) {
    //             return $this->ResponseERROR(
    //                 "Devise {$request->currency} non supportée par {$request->gateway}"
    //             );
    //         }

    //         $depositRequest = new DepositRequest(
    //             user: $request->user(),
    //             amount: $request->amount,
    //             currency: $request->currency,
    //             metadata: [
    //                 'phone' => $request->phone,
    //                 'source' => $request->header('User-Agent'),
    //                 'ip' => $request->ip()
    //             ]
    //         );

    //         $response = $gateway->initiateDeposit($depositRequest);

    //         if (!$response->success) {
    //             return $this->ResponseERROR('Échec de l\'initiation du dépôt');
    //         }

    //         return $this->ResponseOK('Dépôt initié', [
    //             'transaction_id' => $response->transactionId,
    //             'payment_url' => $response->paymentUrl,
    //             'status' => $response->status,
    //             'reference' => $response->reference
    //         ]);

    //     } catch (\Exception $e) {
    //         return $this->ResponseServerError('Erreur', $e->getMessage());
    //     }
    // }

    /**
     * Initier un retrait
     */
    // public function withdraw(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'gateway' => 'required|string|in:fedapay',
    //         'amount' => 'required|numeric|min:53500',
    //         'currency' => 'required|string|size:3',
    //         'destination_type' => 'required|in:mobile_money,bank_account',
    //         'phone' => 'required_if:destination_type,mobile_money',
    //         'account_number' => 'required_if:destination_type,bank_account',
    //         'provider' => 'required_if:destination_type,mobile_money|in:mtn,moov,togocel,wave'
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->ResponseERROR('Validation échouée', $validator->errors());
    //     }

    //     try {
    //         $gateway = $this->gatewayManager->getGateway($request->gateway);

    //         if (!$gateway->supportsWithdrawals()) {
    //             return $this->ResponseERROR("{$request->gateway} ne supporte pas les retraits");
    //         }

    //         $destinationDetails = [
    //             'phone' => $request->phone,
    //             'provider' => $request->provider,
    //             'country_code' => $request->country ?? 'TG'
    //         ];

    //         if ($request->destination_type === 'bank_account') {
    //             $destinationDetails = [
    //                 'account_number' => $request->account_number,
    //                 'bank_code' => $request->bank_code,
    //                 'account_name' => $request->account_name
    //             ];
    //         }

    //         $withdrawalRequest = new WithdrawalRequest(
    //             user: $request->user(),
    //             amount: $request->amount,
    //             currency: $request->currency,
    //             destinationType: $request->destination_type,
    //             destinationDetails: $destinationDetails,
    //             metadata: [
    //                 'source' => $request->header('User-Agent'),
    //                 'ip' => $request->ip()
    //             ]
    //         );

    //         $response = $gateway->initiateWithdrawal($withdrawalRequest);

    //         return $this->ResponseOk($response->message ?? 'Retrait initié', [
    //             'withdrawal_id' => $response->withdrawalId,
    //             'status' => $response->status
    //         ]);

    //     } catch (\Exception $e) {
    //         return $this->ResponseServerError('Erreur', $e->getMessage());
    //     }
    // }

    public function callback(Request $request, $gateway)
    {
        $origin = $request->query('origin', 'app'); // Par défaut 'app' si rien n'est passé
        $status = $request->query('status');

        // On prépare la notification Filament (elle s'affichera sur le panel de destination)
        if ($status === 'approved') {
            Notification::make()
                ->title('Paiement réussi !')
                ->success()
                ->body('Votre dépôt est en cours de validation.')
                ->send();
        } else {
            Notification::make()
                ->title('Paiement échoué')
                ->danger()
                ->send();
        }

        // Redirection dynamique vers le bon panel Filament
        // Si origin = 'admin' -> /admin/transactions
        // Si origin = 'app'   -> /app/transactions
        return redirect()->to("/{$origin}/my-wallet");
    }
    /**
     * Webhook unifié pour tous les gateways
     */
    public function webhook(Request $request, string $gateway)
    {
        try {
            $gatewayInstance = $this->gatewayManager->getGateway($gateway);

            $webhookRequest = new WebhookRequest(
                payload: $request->all(),
                headers: $request->headers->all()
            );

            $response = $gatewayInstance->handleWebhook($webhookRequest);

            return response()->json([
                'status' => $response->status,
                'message' => $response->message
            ]);
        } catch (\Exception $e) {
            Log::error("Webhook error for {$gateway}: " . $e->getMessage());
            return response()->json(['error' => 'Internal error'], 500);
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function status(Request $request, string $gateway, string $transactionId)
    {
        try {
            $gatewayInstance = $this->gatewayManager->getGateway($gateway);

            $status = $gatewayInstance->checkStatus($transactionId);

            return $this->ResponseOk('Statut récupéré', $status);
        } catch (\Exception $e) {
            return $this->ResponseServerError('Erreur', $e->getMessage());
        }
    }

    /**
     * Lister les gateways disponibles
     */
    // public function gateways(Request $request)
    // {
    //     $gateways = [];

    //     foreach ($this->gatewayManager->getAvailableGateways() as $name => $gateway) {
    //         $gateways[$name] = [
    //             'name' => $gateway->getName(),
    //             'supports_withdrawals' => $gateway->supportsWithdrawals(),
    //             'currencies' => $gateway->getSupportedCurrencies()
    //         ];
    //     }

    //     return $this->ResponseOk('Gateways disponibles', $gateways);
    // }
}
