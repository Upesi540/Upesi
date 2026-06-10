<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Récupère la devise XOF
        $currencyCode = Currency::where('code', config('app.base_currency'))->first();

        if (!$currencyCode) {
            $currency = config('app.base_currency');
            $this->command->error("❌ Devise {$currency} non trouvée !");
            return;
        }

        // Wallet de COMMISSIONS
        Wallet::firstOrCreate(
            [
                'holder_type' => 'system_commission',
                'currency_id' => $currencyCode->id,
            ],
            [
                'user_id' => null,
                'available_balance' => 0,
                'frozen_balance' => 0,
                'is_active' => true,
            ]
        );

        // Wallet de SÉQUESTRE
        Wallet::firstOrCreate(
            [
                'holder_type' => 'system_escrow',
                'currency_id' => $currencyCode->id,
            ],
            [
                'user_id' => null,
                'available_balance' => 0,
                'frozen_balance' => 0,
                'is_active' => true,
            ]
        );
            $currency = config('app.base_currency');

        $this->command->info("✅ Portefeuilles système créés en {$currency}.");
    }
}
