<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\MerchantProfile;
use App\Models\Product;
use App\Models\Currency;
use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $buyers = User::role('super_admin')->get();
        $merchantProfiles = MerchantProfile::where('status', 'approved')->get();
        $currency = Currency::where('code', 'XOF')->first();

        if ($buyers->isEmpty() || $merchantProfiles->isEmpty() || !$currency) {
            $this->command->error('Données manquantes');
            return;
        }

        $faker = \Faker\Factory::create('fr_FR');
        $totalOrders = 500;
        $batchSize = 50;
        $ordersData = [];
        $itemsData = [];

        $this->command->info("Génération de $totalOrders commandes multi-vendeurs...");
        $bar = $this->command->getOutput()->createProgressBar($totalOrders);
        $bar->start();

        $createdOrders = 0;

        while ($createdOrders < $totalOrders) {
            $buyer = $buyers->random();

            // Choisir entre 1 et 3 vendeurs pour cette commande
            $numSellers = rand(1, min(3, $merchantProfiles->count()));
            $selectedSellers = $merchantProfiles->random($numSellers);

            $allItems = [];
            $totalAmount = 0;
            $totalServiceFee = 0;
            $totalShipping = 0;

            foreach ($selectedSellers as $seller) {
                $sellerProducts = Product::where('merchant_profile_id', $seller->id)
                    ->where('status', 'active')
                    ->get();

                if ($sellerProducts->isEmpty()) {
                    continue;
                }

                $itemCountForSeller = rand(1, 3);

                for ($i = 0; $i < $itemCountForSeller; $i++) {
                    $product = $sellerProducts->random();
                    $quantity = rand(1, 10);
                    $unitPrice = $product->unit_price;
                    $subtotal = $unitPrice * $quantity;
                    $totalAmount += $subtotal;

                    // Commission selon type de vendeur
                    $commissionRate = match($seller->type) {
                        'producer' => 3.0,
                        'supplier' => 5.0,
                        'transporter' => 2.0,
                        default => 5.0,
                    };
                    $commissionAmount = ($subtotal * $commissionRate) / 100;
                    $sellerGets = $subtotal - $commissionAmount;

                    $statusOptions = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
                    $sellerStatus = Arr::random($statusOptions);

                    $allItems[] = [
                        'product' => $product,
                        'merchant_profile_id' => $seller->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                        'commission_rate' => $commissionRate,
                        'commission_amount' => $commissionAmount,
                        'seller_gets' => $sellerGets,
                        'seller_status' => $sellerStatus,
                    ];
                }

                $totalServiceFee += $totalAmount * 0.05;
                $totalShipping += rand(0, 5000) / 100;
            }

            if (empty($allItems)) {
                continue;
            }

            $tax = rand(0, 5000) / 100;
            $discount = rand(0, 5000) / 100;
            $total = $totalAmount + $tax + $totalShipping + $totalServiceFee - $discount;

            // Statut global de la commande
            $statusOptions = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'partial_cancelled'];
            $status = Arr::random($statusOptions);
            $paymentStatusOptions = ['pending', 'paid', 'failed', 'refunded', 'partial_refund'];
            $paymentStatus = Arr::random($paymentStatusOptions);

            $orderedAt = $faker->dateTimeBetween('-6 months', 'now');
            $confirmedAt = in_array($status, ['processing', 'shipped', 'delivered']) ? (clone $orderedAt)->modify('+'.rand(1, 2).' days') : null;
            $shippedAt = in_array($status, ['shipped', 'delivered']) ? ($confirmedAt ?: (clone $orderedAt))->modify('+'.rand(1, 3).' days') : null;
            $deliveredAt = $status === 'delivered' ? ($shippedAt ?: (clone $orderedAt))->modify('+'.rand(1, 5).' days') : null;
            $cancelledAt = $status === 'cancelled' ? (clone $orderedAt)->modify('+'.rand(1, 5).' days') : null;

            // Création de la commande parent (sans merchant_profile_id)
            $order = [
                'id' => (string) Str::uuid(),
                'order_number' => ReferenceGenerator::generate('ORD', 8),
                'buyer_id' => $buyer->id,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'payment_method_id' => null,
                'payment_reference' => null,
                'subtotal' => $totalAmount,
                'tax' => $tax,
                'shipping_cost' => $totalShipping,
                'service_fee' => $totalServiceFee,
                'discount' => $discount,
                'total' => $total,
                'currency_id' => $currency->id,
                'shipping_address' => json_encode([
                    'address_line1' => $faker->streetAddress,
                    'city' => $faker->city,
                    'region' => $faker->region,
                    'postal_code' => $faker->postcode,
                    'country' => 'GA',
                ]),
                'billing_address' => null,
                'address_id' => null,
                'notes' => $faker->optional()->sentence,
                'metadata' => json_encode(['seller_count' => count($selectedSellers)]),
                'ordered_at' => $orderedAt,
                'confirmed_at' => $confirmedAt,
                'shipped_at' => $shippedAt,
                'delivered_at' => $deliveredAt,
                'cancelled_at' => $cancelledAt,
                'cancelled_by' => null,
                'cancellation_reason' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ];

            $ordersData[] = $order;

            // Créer les items pour cette commande
            foreach ($allItems as $item) {
                $itemConfirmedAt = null;
                $itemShippedAt = null;
                $itemDeliveredAt = null;
                $itemPaidAt = null;

                if (in_array($item['seller_status'], ['confirmed', 'shipped', 'delivered'])) {
                    $itemConfirmedAt = (clone $orderedAt)->modify('+'.rand(1, 2).' days');
                }
                if (in_array($item['seller_status'], ['shipped', 'delivered'])) {
                    $itemShippedAt = $itemConfirmedAt ? (clone $itemConfirmedAt)->modify('+'.rand(1, 3).' days') : (clone $orderedAt)->modify('+'.rand(1, 3).' days');
                }
                if ($item['seller_status'] === 'delivered') {
                    $itemDeliveredAt = $itemShippedAt ? (clone $itemShippedAt)->modify('+'.rand(1, 5).' days') : (clone $orderedAt)->modify('+'.rand(2, 7).' days');
                    $itemPaidAt = $itemDeliveredAt;
                }

                $itemsData[] = [
                    'id' => (string) Str::uuid(),
                    'order_id' => $order['id'],
                    'product_id' => $item['product']->id,
                    'merchant_profile_id' => $item['merchant_profile_id'],
                    'product_name' => $item['product']->title,
                    'quantity' => $item['quantity'],
                    'unit_id' => $item['product']->unit_id,
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'tax' => 0,
                    'discount' => 0,
                    'total' => $item['subtotal'],
                    'seller_status' => $item['seller_status'],
                    'seller_confirmed_at' => $itemConfirmedAt,
                    'seller_shipped_at' => $itemShippedAt,
                    'seller_delivered_at' => $itemDeliveredAt,
                    'seller_paid_at' => $itemPaidAt,
                    'tracking_number' => in_array($item['seller_status'], ['shipped', 'delivered']) ? 'TRK-' . strtoupper(Str::random(10)) : null,
                    'shipping_carrier' => in_array($item['seller_status'], ['shipped', 'delivered']) ? Arr::random(['DHL', 'FedEx', 'UPS', 'La Poste']) : null,
                    'commission_rate' => $item['commission_rate'],
                    'commission_amount' => $item['commission_amount'],
                    'seller_gets' => $item['seller_gets'],
                    'metadata' => null,
                    'is_custom_item' => false,
                    'custom_description' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($ordersData) >= $batchSize) {
                Order::insert($ordersData);
                OrderItem::insert($itemsData);
                $ordersData = [];
                $itemsData = [];
                $bar->advance($batchSize);
                $createdOrders += $batchSize;
            } else {
                $bar->advance();
                $createdOrders++;
            }
        }

        if (!empty($ordersData)) {
            Order::insert($ordersData);
            OrderItem::insert($itemsData);
            $bar->advance(count($ordersData));
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('OrderSeeder terminé !');
        $this->command->info('Commandes multi-vendeurs générées.');
    }
}
