<?php

namespace Database\Seeders;

// use App\Models\User;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            NewsCategorySeeder::class,
            //Site content
            ProjectSeeder::class,
            PartnerSeeder::class,
            TeamMemberSeeder::class,

            //E-commerce
            CurrencySeeder::class,
            WalletSystemSeeder::class,
            PaymentMethodSeeder::class,
            MarketSeeder::class,
            CategorySeeder::class,
            // CountrySeeder::class,  // Uncomment if you want to seed countries but already seeded using api
            UnitsSeeder::class,
            CropSeeder::class,
            RolesSeeder::class,
            SuperAdminSeeder::class,
            ServiceCategorySeeder::class,
            ServiceSeeder::class,
            SlideSeeder::class,
            VehicleTypeSeeder::class
        ]);
    }
}
