<?php

namespace App\Traits;

use App\Models\BulkTransaction;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasBulkTransactions
{
    /**
     * Transactions groupées où l'utilisateur est trader
     */
    public function bulkTransactionsAsTrader(): HasMany
    {
        return $this->hasMany(BulkTransaction::class, 'trader_id');
    }

    /**
     * Transactions groupées où l'utilisateur est contrepartie
     */
    public function bulkTransactionsAsCounterparty(): HasMany
    {
        return $this->hasMany(BulkTransaction::class, 'counterparty_id');
    }

    /**
     * Vérifier si l'utilisateur est un trader (négociant)
     */
    public function isTrader(): bool
    {
        return $this->merchantProfiles()
            ->where('type', 'trader')
            ->exists();
    }

    /**
     * Récupérer les ventes groupées du trader
     */
    public function getGroupedSales()
    {
        return $this->bulkTransactionsAsTrader()
            ->where('type', 'sale')
            ->with('details')
            ->get();
    }

    /**
     * Récupérer les achats groupés du trader
     */
    public function getGroupedPurchases()
    {
        return $this->bulkTransactionsAsTrader()
            ->where('type', 'purchase')
            ->with('details')
            ->get();
    }

    /**
     * Récupérer les transactions groupées en attente de validation
     */
    public function getPendingBulkTransactions()
    {
        return $this->bulkTransactionsAsTrader()
            ->where('status', 'pending')
            ->get();
    }
}
