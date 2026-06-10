<?php

namespace App\Services;

use App\Helpers\ReferenceGenerator;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Deposit funds into wallet
     */
    // WalletService.php
    public function deposit(Wallet $wallet, float $amount, float $fee, string $reference, ?Model $source = null): WalletTransaction
    {
        $netAmount = $amount - $fee;  // 🔥 Net après commission

        return $wallet->modifyBalance(
            amount: $netAmount,  // 9 900 FCFA au lieu de 10 000
            type: 'credit',
            opType: 'deposit',
            desc: "Deposit. Ref: $reference (fee: $fee)",
            source: $source
        );
    }
    /**
     * Withdraw funds from wallet
     */
    public function withdraw(Wallet $wallet, float $amount, string $reference, ?Model $source = null): WalletTransaction
    {
        return $wallet->modifyBalance(
            amount: $amount,
            type: 'debit',
            opType: 'withdrawal',
            desc: "Withdrawal. Ref: $reference",
            source: $source
        );
    }

    /**
     * Transfer between wallets
     */
    public function transfer(Wallet $from, Wallet $to, float $amount, string $reason = 'Transfer'): void
    {
        if ($from->currency_id !== $to->currency_id) {
            throw new \Exception("Currency mismatch.");
        }

        DB::transaction(function () use ($from, $to, $amount, $reason) {
            $from->modifyBalance($amount, 'debit', 'transfer_out', "To {$to->user_id}: $reason");
            $to->modifyBalance($amount, 'credit', 'transfer_in', "From {$from->user_id}: $reason");
        });
    }

    /**
     * Hold funds in escrow (freeze)
     */
    public function holdFunds(Wallet $buyerWallet, float $amount, Model $reference, string $sellerId, string $description): WalletTransaction
    {
        return DB::transaction(function () use ($buyerWallet, $amount, $reference, $sellerId, $description) {
            if (!$buyerWallet->hasSufficientBalance($amount)) {
                throw new \Exception("Insufficient balance. Required: {$amount}, Available: {$buyerWallet->available_balance}");
            }

            $before = $buyerWallet->available_balance;
            $buyerWallet->available_balance -= $amount;
            $buyerWallet->save();

            return $buyerWallet->transactions()->create([
                'currency_id' => $buyerWallet->currency_id,
                'reference' => ReferenceGenerator::generate('HLD'),
                'type' => 'freeze',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $buyerWallet->available_balance,
                'operation_type' => 'escrow_hold',
                'description' => $description,
                'transactionable_type' => get_class($reference),
                'transactionable_id' => $reference->id,
                'status' => 'pending',
                'metadata' => [
                    'seller_id' => $sellerId,
                    'expires_at' => now()->addDays(14)->toIso8601String()
                ]
            ]);
        });
    }

    /**
     * Release held funds to seller
     */
    public function releaseFunds(WalletTransaction $holdTransaction, float $feePercent = 0): WalletTransaction
    {
        return DB::transaction(function () use ($holdTransaction, $feePercent) {
            if ($holdTransaction->type !== 'freeze' || $holdTransaction->status !== 'pending') {
                throw new \Exception("Invalid hold transaction.");
            }

            $buyerWallet = $holdTransaction->wallet;
            $sellerId = $holdTransaction->metadata['seller_id'] ?? null;

            if (!$sellerId) {
                throw new \Exception("Seller not found in hold transaction.");
            }

            $sellerWallet = Wallet::where('user_id', $sellerId)->first();
            if (!$sellerWallet) {
                throw new \Exception("Seller wallet not found.");
            }

            $amount = $holdTransaction->amount;
            $fee = ($amount * $feePercent) / 100;
            $netAmount = $amount - $fee;

            // 1. Mark hold as completed
            $holdTransaction->update([
                'status' => 'completed',
                'metadata' => array_merge($holdTransaction->metadata ?? [], [
                    'released_at' => now()->toIso8601String(),
                    'fee_percent' => $feePercent,
                    'fee' => $fee,
                    'net_amount' => $netAmount,
                    'seller_wallet_id' => $sellerWallet->id
                ])
            ]);

            // 2. Debit buyer (finalize the payment)
            $buyerDebit = $buyerWallet->modifyBalance(
                amount: $amount,
                type: 'debit',
                opType: 'purchase',
                desc: "Purchase #{$holdTransaction->transactionable_id}",
                source: $holdTransaction->transactionable
            );

            // 3. Credit seller
            $sellerCredit = $sellerWallet->modifyBalance(
                amount: $netAmount,
                type: 'credit',
                opType: 'sale',
                desc: "Sale #{$holdTransaction->transactionable_id}",
                source: $holdTransaction->transactionable
            );

            // 4. Platform commission
            if ($fee > 0) {
                $this->chargeCommission($buyerWallet->currency_id, $fee, $holdTransaction->transactionable);
            }

            return $sellerCredit;
        });
    }

    /**
     * Refund held funds to buyer
     */
    public function refundFunds(WalletTransaction $holdTransaction, string $reason): WalletTransaction
    {
        return DB::transaction(function () use ($holdTransaction, $reason) {
            if ($holdTransaction->type !== 'freeze' || $holdTransaction->status !== 'pending') {
                throw new \Exception("Invalid hold transaction.");
            }

            $buyerWallet = $holdTransaction->wallet;
            $amount = $holdTransaction->amount;

            // 1. Mark hold as cancelled
            $holdTransaction->update([
                'status' => 'cancelled',
                'metadata' => array_merge($holdTransaction->metadata ?? [], [
                    'refunded_at' => now()->toIso8601String(),
                    'refund_reason' => $reason
                ])
            ]);

            // 2. Unfreeze and return to buyer
            $buyerWallet->available_balance += $amount;
            $buyerWallet->save();

            $before = $buyerWallet->available_balance - $amount;

            // 3. Create refund transaction
            return $buyerWallet->transactions()->create([
                'currency_id' => $buyerWallet->currency_id,
                'reference' => ReferenceGenerator::generate('REF'),
                'type' => 'unfreeze',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $buyerWallet->available_balance,
                'operation_type' => 'escrow_refund',
                'description' => "Refund: {$reason}",
                'transactionable_type' => $holdTransaction->transactionable_type,
                'transactionable_id' => $holdTransaction->transactionable_id,
                'status' => 'completed',
                'metadata' => [
                    'hold_transaction_id' => $holdTransaction->id,
                    'refund_reason' => $reason
                ]
            ]);
        });
    }

    /**
     * Direct purchase (no escrow, immediate payment)
     */
    public function directPurchase(Wallet $buyer, Wallet $seller, float $amount, Model $reference, float $feePercent = 5.0): void
    {
        DB::transaction(function () use ($buyer, $seller, $amount, $reference, $feePercent) {
            $fee = ($amount * $feePercent) / 100;
            $netAmount = $amount - $fee;

            $buyer->modifyBalance($amount, 'debit', 'purchase', "Purchase #{$reference->id}", $reference);
            $seller->modifyBalance($netAmount, 'credit', 'sale', "Sale #{$reference->id}", $reference);

            if ($fee > 0) {
                $this->chargeCommission($buyer->currency_id, $fee, $reference);
            }
        });
    }

    /**
     * Find pending hold for a reference
     */
    public function findPendingHold(Model $reference): ?WalletTransaction
    {
        return WalletTransaction::where('type', 'freeze')
            ->where('status', 'pending')
            ->where('transactionable_type', get_class($reference))
            ->where('transactionable_id', $reference->id)
            ->first();
    }

    /**
     * Get all transactions for a reference
     */
    public function getTransactionsForReference(Model $reference): \Illuminate\Database\Eloquent\Collection
    {
        return WalletTransaction::where('transactionable_type', get_class($reference))
            ->where('transactionable_id', $reference->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get wallet balance with details
     */
    public function getBalanceDetails(Wallet $wallet): array
    {
        $pendingHolds = $wallet->transactions()
            ->where('type', 'freeze')
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'available_balance' => $wallet->available_balance,
            'pending_holds' => $pendingHolds,
            'total_balance' => $wallet->available_balance + $pendingHolds,
            'currency' => $wallet->currency->code ?? 'XOF'
        ];
    }

    /**
     * Charge platform commission
     *
     * @param string $currencyId
     * @param float $amount
     * @param Model $source  // ← Changé : string → Model
     */
    private function chargeCommission(string $currencyId, float $amount, Model $source): void
    {
        $adminWallet = Wallet::getSystemCommission($currencyId);

        if ($adminWallet) {
            $adminWallet->modifyBalance(
                amount: $amount,
                type: 'credit',
                opType: 'commission',
                desc: "Commission for reference #{$source->id}",
                source: $source  // ← Maintenant passé correctement
            );
        }
    }
}
