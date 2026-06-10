<?php

namespace Database\Seeders;

use App\Helpers\ReferenceGenerator;
use App\Models\Currency;
use App\Models\MerchantProfile;
use App\Models\ServiceOffer;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération des acheteurs (clients)
        $buyers = User::role('customer')->get();

        // Récupération des profils marchands pouvant offrir des services (transporteur ou prestataire)
        $serviceProfiles = MerchantProfile::where('status', 'approved')
            ->whereIn('type', ['transporter', 'provider', 'supplier'])
            ->get();

        // Récupération des offres de service associées à ces profils
        $serviceOffers = ServiceOffer::whereIn('merchant_profile_id', $serviceProfiles->pluck('id'))
            ->where('status', 'active')
            ->get();

        // Récupération de la devise (XOF)
        $currency = Currency::where('code', 'XOF')->first();
        if (!$currency) {
            $currency = Currency::where('is_base', true)->first();
        }

        if ($buyers->isEmpty() || $serviceProfiles->isEmpty() || $serviceOffers->isEmpty()) {
            $this->command->error('Données manquantes : acheteurs, profils de services ou offres de service.');
            return;
        }

        $faker = \Faker\Factory::create('fr_FR');
        $totalRequests = 200;
        $batchSize = 50;
        $requestsData = [];

        $this->command->info("Génération de $totalRequests demandes de service...");
        $bar = $this->command->getOutput()->createProgressBar($totalRequests);
        $bar->start();

        for ($i = 0; $i < $totalRequests; $i++) {
            // Choisir un acheteur aléatoire
            $buyer = $buyers->random();

            // Choisir une offre de service aléatoire
            $offer = $serviceOffers->random();
            $merchantProfile = $offer->merchantProfile;

            // Déterminer les détails en fonction du type de profil
            $details = [];
            if ($merchantProfile->type === 'transporter') {
                $details = [
                    'pickup_address' => $faker->streetAddress . ', ' . $faker->city,
                    'delivery_address' => $faker->streetAddress . ', ' . $faker->city,
                    'distance_km' => $faker->randomFloat(1, 5, 500),
                    'weight_kg' => $faker->randomFloat(1, 10, 1000),
                    'vehicle_type' => Arr::random(['Camion', 'Pickup', 'Bétaillère', 'Remorque']),
                ];
            } elseif ($merchantProfile->type === 'provider') {
                $details = [
                    'area_hectares' => $faker->randomFloat(1, 0.5, 50),
                    'location' => $faker->city,
                    'service_date' => $faker->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
                    'equipment_needed' => $faker->boolean(70),
                ];
            } else {
                $details = [
                    'product_type' => Arr::json_encode(['Semences', 'Engrais', 'Pesticides']),
                    'quantity' => $faker->randomFloat(1, 10, 500),
                    'delivery_required' => $faker->boolean(80),
                ];
            }

            // Statuts possibles
            $statusOptions = ['pending', 'accepted', 'in_progress', 'completed', 'cancelled', 'rejected'];
            $status = Arr::random($statusOptions);

            // Prix selon le statut
            $basePrice = $offer->price ?? $faker->randomFloat(2, 5000, 500000);
            $quotedPrice = null;
            $finalPrice = null;

            if (in_array($status, ['accepted', 'in_progress', 'completed'])) {
                $quotedPrice = $basePrice;
                $finalPrice = $quotedPrice;
            } elseif ($status === 'rejected' && $faker->boolean(50)) {
                $quotedPrice = $basePrice;
            }

            // Dates
            $scheduledAt = null;
            $startedAt = null;
            $completedAt = null;
            $acceptedAt = null;
            $rejectedAt = null;
            $cancelledAt = null;
            $cancelledBy = null;
            $cancellationReason = null;

            $orderedAt = $faker->dateTimeBetween('-6 months', 'now');

            if (in_array($status, ['accepted', 'in_progress', 'completed'])) {
                $acceptedAt = (clone $orderedAt)->modify('+' . rand(1, 2) . ' days');
                $scheduledAt = $acceptedAt ? (clone $acceptedAt)->modify('+' . rand(3, 10) . ' days') : (clone $orderedAt)->modify('+' . rand(3, 10) . ' days');
            }

            if (in_array($status, ['in_progress', 'completed'])) {
                $startedAt = $scheduledAt ? (clone $scheduledAt)->modify('-' . rand(0, 2) . ' days') : (clone $orderedAt)->modify('+' . rand(5, 15) . ' days');
            }

            if ($status === 'completed') {
                $completedAt = $startedAt ? (clone $startedAt)->modify('+' . rand(1, 5) . ' days') : (clone $orderedAt)->modify('+' . rand(10, 20) . ' days');
            }

            if ($status === 'rejected') {
                $rejectedAt = (clone $orderedAt)->modify('+' . rand(1, 3) . ' days');
            }

            if ($status === 'cancelled') {
                $cancelledAt = (clone $orderedAt)->modify('+' . rand(1, 5) . ' days');
                $cancelledBy = Arr::random(['buyer', 'provider', 'admin']);
                $cancellationReason = $faker->sentence(rand(3, 8));
            }

            // Description aléatoire
            $description = $faker->sentence(rand(5, 15));


            $lastNumber = str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            // Création de la demande
            $requestData = [
                'id' => (string) Str::uuid(),
                'request_number' => ReferenceGenerator::generate('SRV', 8),
                'buyer_id' => $buyer->id,
                'merchant_profile_id' => $merchantProfile->id,
                'service_offer_id' => $offer->id,
                'status' => $status,
                'description' => $description,
                'details' => json_encode($details),
                'quoted_price' => $quotedPrice,
                'final_price' => $finalPrice,
                'scheduled_at' => $scheduledAt,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'cancelled_by' => $cancelledBy,
                'cancellation_reason' => $cancellationReason,
                'cancelled_at' => $cancelledAt,
                'accepted_at' => $acceptedAt,
                'rejected_at' => $rejectedAt,
                'currency_id' => $currency?->id,
                'created_at' => $orderedAt,
                'updated_at' => now(),
                'deleted_at' => null,
            ];

            $requestsData[] = $requestData;

            if (count($requestsData) >= $batchSize) {
                ServiceRequest::insert($requestsData);
                $requestsData = [];
                $bar->advance($batchSize);
            } else {
                $bar->advance();
            }
        }

        // Insérer le reste
        if (!empty($requestsData)) {
            ServiceRequest::insert($requestsData);
            $bar->advance(count($requestsData));
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('ServiceRequestSeeder terminé !');
        $this->command->info('Demandes de service générées avec toutes les colonnes.');
    }
}
