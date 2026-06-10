<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Devises africaines (CFA)
        $currencies = [
            // DEVISE DE BASE (USD)
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'exchange_rate' => 1.0000,
                'is_base' => true,
                'is_active' => true
            ],

            // DEVISES MONDIALES MAJEURES
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'symbol' => '€',
                'exchange_rate' => 1.0900, // 1 EUR = 1.09 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'GBP',
                'name' => 'British Pound',
                'symbol' => '£',
                'exchange_rate' => 1.2700, // 1 GBP = 1.27 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'JPY',
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'exchange_rate' => 0.0067, // 1 JPY = 0.0067 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'CNY',
                'name' => 'Chinese Yuan',
                'symbol' => '¥',
                'exchange_rate' => 0.1380, // 1 CNY = 0.138 USD
                'is_base' => false,
                'is_active' => true
            ],

            // AFRIQUE DE L'OUEST (UEMOA)
            [
                'code' => 'XOF',
                'name' => 'Franc CFA (UEMOA)',
                'symbol' => 'FCFA',
                'exchange_rate' => 0.0016, // 1 XOF = 0.0016 USD (fixe par rapport à l'EUR)
                'is_base' => false,
                'is_active' => true
            ],

            // AFRIQUE CENTRALE (CEMAC)
            [
                'code' => 'XOF',
                'name' => 'Franc CFA (CEMAC)',
                'symbol' => 'FCFA',
                'exchange_rate' => 0.0016, // 1 XOF = 0.0016 USD (fixe par rapport à l'EUR)
                'is_base' => false,
                'is_active' => true
            ],

            // AUTRES DEVISES AFRICAINES
            [
                'code' => 'NGN',
                'name' => 'Nigerian Naira',
                'symbol' => '₦',
                'exchange_rate' => 0.00065, // 1 NGN = 0.00065 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'GHS',
                'name' => 'Ghanaian Cedi',
                'symbol' => '₵',
                'exchange_rate' => 0.0720, // 1 GHS = 0.072 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'KES',
                'name' => 'Kenyan Shilling',
                'symbol' => 'KSh',
                'exchange_rate' => 0.0067, // 1 KES = 0.0067 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'ZAR',
                'name' => 'South African Rand',
                'symbol' => 'R',
                'exchange_rate' => 0.0530, // 1 ZAR = 0.053 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'MAD',
                'name' => 'Moroccan Dirham',
                'symbol' => 'DH',
                'exchange_rate' => 0.0990, // 1 MAD = 0.099 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'TND',
                'name' => 'Tunisian Dinar',
                'symbol' => 'DT',
                'exchange_rate' => 0.3180, // 1 TND = 0.318 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'EGP',
                'name' => 'Egyptian Pound',
                'symbol' => 'E£',
                'exchange_rate' => 0.0207, // 1 EGP = 0.0207 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'ETB',
                'name' => 'Ethiopian Birr',
                'symbol' => 'Br',
                'exchange_rate' => 0.0176, // 1 ETB = 0.0176 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'UGX',
                'name' => 'Ugandan Shilling',
                'symbol' => 'USh',
                'exchange_rate' => 0.00026, // 1 UGX = 0.00026 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'TZS',
                'name' => 'Tanzanian Shilling',
                'symbol' => 'TSh',
                'exchange_rate' => 0.00038, // 1 TZS = 0.00038 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'RWF',
                'name' => 'Rwandan Franc',
                'symbol' => 'FRw',
                'exchange_rate' => 0.00077, // 1 RWF = 0.00077 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'BIF',
                'name' => 'Burundian Franc',
                'symbol' => 'FBu',
                'exchange_rate' => 0.00035, // 1 BIF = 0.00035 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'CDF',
                'name' => 'Congolese Franc',
                'symbol' => 'FC',
                'exchange_rate' => 0.00036, // 1 CDF = 0.00036 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'AOA',
                'name' => 'Angolan Kwanza',
                'symbol' => 'Kz',
                'exchange_rate' => 0.0011, // 1 AOA = 0.0011 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'MZN',
                'name' => 'Mozambican Metical',
                'symbol' => 'MTn',
                'exchange_rate' => 0.0156, // 1 MZN = 0.0156 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'ZMW',
                'name' => 'Zambian Kwacha',
                'symbol' => 'ZK',
                'exchange_rate' => 0.0360, // 1 ZMW = 0.036 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'MWK',
                'name' => 'Malawian Kwacha',
                'symbol' => 'MK',
                'exchange_rate' => 0.00057, // 1 MWK = 0.00057 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'MUR',
                'name' => 'Mauritian Rupee',
                'symbol' => '₨',
                'exchange_rate' => 0.0217, // 1 MUR = 0.0217 USD
                'is_base' => false,
                'is_active' => true
            ],

            // AUTRES DEVISES INTERNATIONALES UTILES
            [
                'code' => 'CHF',
                'name' => 'Swiss Franc',
                'symbol' => 'Fr',
                'exchange_rate' => 1.1200, // 1 CHF = 1.12 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'symbol' => 'C$',
                'exchange_rate' => 0.7300, // 1 CAD = 0.73 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'AUD',
                'name' => 'Australian Dollar',
                'symbol' => 'A$',
                'exchange_rate' => 0.6500, // 1 AUD = 0.65 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'BRL',
                'name' => 'Brazilian Real',
                'symbol' => 'R$',
                'exchange_rate' => 0.2000, // 1 BRL = 0.20 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'INR',
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'exchange_rate' => 0.0120, // 1 INR = 0.012 USD
                'is_base' => false,
                'is_active' => true
            ],
            [
                'code' => 'RUB',
                'name' => 'Russian Ruble',
                'symbol' => '₽',
                'exchange_rate' => 0.0110, // 1 RUB = 0.011 USD
                'is_base' => false,
                'is_active' => true
            ],
        ];

        // Insérer ou mettre à jour les devises
        foreach ($currencies as $currency) {
            Currency::firstOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }

        // Message de confirmation
        $this->command->info('✅ ' . count($currencies) . ' devises ont été créées avec succès !');
        $this->command->info('💵 Devise de base: USD (1 USD = 1 USD)');
        $this->command->info('🌍 Devises africaines disponibles: ' .
            Currency::whereIn('code', ['XOF', 'XOF', 'NGN', 'GHS', 'KES', 'ZAR', 'MAD', 'TND', 'EGP', 'ETB', 'UGX', 'TZS', 'RWF', 'BIF', 'CDF', 'AOA', 'MZN', 'ZMW', 'MWK', 'MUR'])->count());
    }
}
