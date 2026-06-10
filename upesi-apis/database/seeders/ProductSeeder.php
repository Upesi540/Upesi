<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Crop;
use App\Models\Product;
use App\Models\State;
use App\Models\Unit;
use App\Models\MerchantProfile;
use App\Models\Currency;
use App\Helpers\ReferenceGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $merchantProfiles = MerchantProfile::all();
        $crops = Crop::all();
        $units = Unit::pluck('id')->toArray();
        $currency = Currency::where('code', 'XOF')->first() ?? Currency::first();

        // Récupérer tous les pays
        $countries = Country::all();

        if ($merchantProfiles->isEmpty() || $crops->isEmpty()) {
            $this->command->error("DANGER : Données manquantes !");
            return;
        }

        $faker = \Faker\Factory::create('fr_FR');

        $total = 2000;
        $batchSize = 500;
        $products = [];

        $this->command->info("Génération de $total produits pour Upesi Market...");

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->start();

        for ($i = 0; $i < $total; $i++) {
            $randomCrop = $crops->random();
            $randomMerchant = $merchantProfiles->random();

            // 1. Sélectionner un pays aléatoire
            $country = $countries->random();

            // 2. Sélectionner un état appartenant à ce pays
            $states = State::where('country_id', $country->id)->pluck('id')->toArray();
            $stateId = !empty($states) ? Arr::random($states) : null;

            // 3. Sélectionner une ville appartenant à cet état
            $cityId = null;
            if ($stateId) {
                $cities = City::where('state_id', $stateId)->pluck('id')->toArray();
                $cityId = !empty($cities) ? Arr::random($cities) : null;
            }

            $products[] = [
                'id'                  => (string) Str::uuid(),
                'merchant_profile_id' => $randomMerchant->id,
                'crop_id'             => $randomCrop->id,
                'currency_id'         => $currency->id,
                'unit_id'             => Arr::random($units),
                'title'               => $this->generateTitle($randomCrop->name),
                'description'         => $faker->paragraph(2),
                'sku'                 => ReferenceGenerator::generate('PRD', 8),
                'images'              => json_encode([
                    "https://loremflickr.com/640/480/agriculture?lock=" . $i,
                ]),
                'quantity'            => $faker->randomFloat(2, 100, 5000),
                'min_order_quantity'  => $faker->randomElement([1, 5, 10]),
                'unit_price'          => $faker->randomFloat(2, 500, 15000),
                'country_id'          => $country->id,
                'state_id'            => $stateId,
                'city_id'             => $cityId,
                'warehouse_name'      => "Dépôt " . $faker->city,
                'address'             => $faker->address,
                'latitude'            => $faker->latitude(-0.5, 0.5),
                'longitude'           => $faker->longitude(9.2, 10.5),
                'status'              => 'active',
                'is_featured'         => $faker->boolean(15),
                'harvest_info'        => json_encode([
                    'harvested_at' => now()->subDays(rand(1, 30))->format('Y-m-d'),
                    'quality_grade' => 'A',
                ]),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            if (count($products) >= $batchSize) {
                Product::insert($products);
                $products = [];
                $bar->advance($batchSize);
            }
        }

        if (!empty($products)) {
            Product::insert($products);
            $bar->advance(count($products));
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("ProductSeeder terminé !");
    }

    private function generateTitle(string $cropName): string
    {
        $adjectives = ['Frais', 'Bio', 'Premium', 'Export', 'Direct'];
        return $cropName . ' - ' . Arr::random($adjectives);
    }
}
