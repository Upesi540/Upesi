<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            // Mobile Money (Afrique)
            [
                'code' => 'orange_money',
                'name' => 'Orange Money',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via Orange Money',
                'logo_url' => '/logos/orange-money.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.5],
                    'limits' => ['min' => 100, 'max' => 500000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['CI', 'SN', 'BF', 'ML', 'NE'],
                'operators' => ['Orange'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 10
            ],
            [
                'code' => 'mtn_money',
                'name' => 'MTN Money',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via MTN Money',
                'logo_url' => '/logos/mtn-money.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.2],
                    'limits' => ['min' => 100, 'max' => 500000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['CI', 'UG', 'RW', 'CM'],
                'operators' => ['MTN'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 11
            ],
            [
                'code' => 'moov_money',
                'name' => 'Moov Money',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via Moov Money',
                'logo_url' => '/logos/moov-money.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.3],
                    'limits' => ['min' => 100, 'max' => 300000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['CI', 'BF', 'TG'],
                'operators' => ['Moov'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 12
            ],
            [
                'code' => 'wave',
                'name' => 'Wave',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via Wave',
                'logo_url' => '/logos/wave.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.0],
                    'limits' => ['min' => 100, 'max' => 1000000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['SN', 'CI', 'BF'],
                'operators' => ['Wave'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 13
            ],
            [
                'code' => 'airtel_money',
                'name' => 'Airtel Money',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via Airtel Money',
                'logo_url' => '/logos/airtel-money.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.4],
                    'limits' => ['min' => 100, 'max' => 400000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['UG', 'RW', 'ZM'],
                'operators' => ['Airtel'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 14
            ],
            [
                'code' => 'm_pesa',
                'name' => 'M-Pesa',
                'category' => 'mobile_money',
                'description' => 'Paiement mobile via M-Pesa',
                'logo_url' => '/logos/m-pesa.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.1],
                    'limits' => ['min' => 100, 'max' => 700000],
                    'processing_time' => 'instant'
                ],
                'countries' => ['KE', 'TZ', 'ZA'],
                'operators' => ['Vodacom', 'Safaricom'],
                'is_instant' => true,
                'requires_phone' => true,
                'sort_order' => 15
            ],

            // Cartes bancaires
            [
                'code' => 'visa',
                'name' => 'Visa',
                'category' => 'card',
                'description' => 'Paiement par carte Visa',
                'logo_url' => '/logos/visa.png',
                'configuration' => [
                    'fees' => ['percentage' => 2.5],
                    'processing_time' => 'instant'
                ],
                'countries' => null, // Tous pays
                'is_instant' => true,
                'requires_account' => false,
                'sort_order' => 30
            ],
            [
                'code' => 'mastercard',
                'name' => 'Mastercard',
                'category' => 'card',
                'description' => 'Paiement par carte Mastercard',
                'logo_url' => '/logos/mastercard.png',
                'configuration' => [
                    'fees' => ['percentage' => 2.5],
                    'processing_time' => 'instant'
                ],
                'countries' => null,
                'is_instant' => true,
                'sort_order' => 31
            ],

            // Virements bancaires
            [
                'code' => 'bank_transfer',
                'name' => 'Virement bancaire',
                'category' => 'bank',
                'description' => 'Virement bancaire classique',
                'logo_url' => '/logos/bank.png',
                'configuration' => [
                    'fees' => ['fixed' => 5],
                    'processing_time' => '1-3 jours'
                ],
                'countries' => null,
                'is_instant' => false,
                'requires_account' => true,
                'sort_order' => 50
            ],
            [
                'code' => 'sepa',
                'name' => 'Virement SEPA',
                'category' => 'bank',
                'description' => 'Virement européen SEPA',
                'logo_url' => '/logos/sepa.png',
                'configuration' => [
                    'fees' => ['fixed' => 1],
                    'processing_time' => '1 jour'
                ],
                'countries' => ['FR', 'DE', 'ES', 'IT', 'BE', 'NL'],
                'is_instant' => false,
                'requires_account' => true,
                'sort_order' => 51
            ],

            // Portefeuilles électroniques
            [
                'code' => 'paypal',
                'name' => 'PayPal',
                'category' => 'wallet',
                'description' => 'Paiement via compte PayPal',
                'logo_url' => '/logos/paypal.png',
                'configuration' => [
                    'fees' => ['percentage' => 3.5],
                    'processing_time' => 'instant'
                ],
                'countries' => null,
                'is_instant' => true,
                'requires_account' => true,
                'sort_order' => 70
            ],
            [
                'code' => 'flutterwave',
                'name' => 'Flutterwave',
                'category' => 'wallet',
                'description' => 'Paiement via Flutterwave (Afrique)',
                'logo_url' => '/logos/flutterwave.png',
                'configuration' => [
                    'fees' => ['percentage' => 2.0],
                    'processing_time' => 'instant'
                ],
                'countries' => ['NG', 'GH', 'KE', 'ZA'],
                'is_instant' => true,
                'requires_account' => true,
                'sort_order' => 71
            ],
            [
                'code' => 'paystack',
                'name' => 'Paystack',
                'category' => 'wallet',
                'description' => 'Paiement via Paystack',
                'logo_url' => '/logos/paystack.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.9],
                    'processing_time' => 'instant'
                ],
                'countries' => ['NG', 'GH'],
                'is_instant' => true,
                'requires_account' => true,
                'sort_order' => 72
            ],

            // Cryptomonnaies
            [
                'code' => 'bitcoin',
                'name' => 'Bitcoin',
                'category' => 'crypto',
                'description' => 'Paiement en Bitcoin',
                'logo_url' => '/logos/bitcoin.png',
                'configuration' => [
                    'fees' => ['percentage' => 1.0],
                    'processing_time' => '30-60 min'
                ],
                'countries' => null,
                'is_instant' => false,
                'requires_account' => true,
                'sort_order' => 90
            ],
            [
                'code' => 'usdt',
                'name' => 'USDT (Tether)',
                'category' => 'crypto',
                'description' => 'Paiement en USDT (stablecoin)',
                'logo_url' => '/logos/usdt.png',
                'configuration' => [
                    'fees' => ['percentage' => 0.5],
                    'processing_time' => '10-30 min'
                ],
                'countries' => null,
                'is_instant' => false,
                'requires_account' => true,
                'sort_order' => 91
            ],

            // Espèces et autres
            [
                'code' => 'cash',
                'name' => 'Espèces',
                'category' => 'cash',
                'description' => 'Paiement en espèces à la livraison',
                'logo_url' => '/logos/cash.png',
                'configuration' => [
                    'fees' => ['fixed' => 0],
                    'processing_time' => 'à la livraison'
                ],
                'countries' => null,
                'is_instant' => false,
                'sort_order' => 100
            ],
            [
                'code' => 'wallet',
                'name' => 'Portefeuille interne',
                'category' => 'wallet',
                'description' => 'Paiement via votre portefeuille sur la plateforme',
                'logo_url' => '/logos/wallet.png',
                'configuration' => [
                    'fees' => ['fixed' => 0],
                    'processing_time' => 'instant'
                ],
                'countries' => null,
                'is_instant' => true,
                'requires_account' => true,
                'sort_order' => 1
            ],
        ];

        foreach ($methods as $method) {
            PaymentMethod::updateOrCreate(
                ['code' => $method['code']],
                $method
            );
        }

        $this->command->info('✅ ' . count($methods) . ' méthodes de paiement créées');
    }
}
