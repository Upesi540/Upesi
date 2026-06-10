<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Country;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le Gabon (pays par défaut)
        $gabon = Country::where('iso2', 'GA')->first();
        if (!$gabon) {
            $gabon = Country::where('name', 'like', '%Gabon%')->first();
        }

        // Récupérer la devise XOF
        $currencyCode = Currency::where('code', config('app.base_currency'))->first();
        if (!$currencyCode) {
            $currencyCode = Currency::where('code', config('app.base_currency'))->first(); // Fallback
        }

        $admin = User::firstOrCreate(
            ['email' => 'upesi@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'phone' => '+24165956357',
                'country_id' => $gabon ? $gabon->id : null,
                'preferred_currency_id' => $currencyCode ? $currencyCode->id : null,
                'password' => Hash::make('12345678'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super_admin');

        // ========== AFFICHAGE DES INSTRUCTIONS ==========
        $this->command->newLine(2);
        $this->command->info('🎉 ' . str_repeat('=', 50));
        $this->command->info('🎉 SUPER ADMIN CRÉÉ AVEC SUCCÈS !');
        $this->command->info('🎉 ' . str_repeat('=', 50));
        $this->command->newLine();

        $this->command->line("   📧 Email: \033[32mkoviamendosse3@gmail.com\033[0m");
        $this->command->line("   🔑 Mot de passe: \033[32m12345678\033[0m");
        $this->command->line("   🌍 Pays: \033[33m" . ($gabon ? $gabon->name : 'Gabon') . "\033[0m");
        $this->command->line("   💰 Devise: \033[33m" . ($currencyCode ? $currencyCode->code : config('app.base_currency')) . "\033[0m");

        $this->command->newLine(2);
        $this->command->warn('⚠️  ⚠️  ⚠️  ACTION REQUISE  ⚠️  ⚠️  ⚠️');
        $this->command->newLine();
        $this->command->line('📌 Pour générer les permissions Filament Shield, exécutez cette commande :');
        $this->command->newLine();
        $this->command->info('   ➜  php artisan shield:generate --all --ignore-existing-policies');
        // $this->command->newLine();
        // $this->command->line('📌 Puis pour lier le super_admin :');
        // $this->command->newLine();
        // $this->command->info('   ➜  php artisan shield:super-admin');
        $this->command->newLine(2);
        $this->command->info('🎉 ' . str_repeat('=', 50));
    }
}
