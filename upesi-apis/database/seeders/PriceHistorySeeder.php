<?php

namespace Database\Seeders;

use App\Models\Crop;
use App\Models\Unit;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PriceHistorySeeder extends Seeder
{
    public function run()
    {
        // 1. Récupération des IDs existants pour les relations
        $crops = Crop::pluck('id')->toArray();
        $countries = Country::pluck('id')->toArray();
        $units = Unit::pluck('id')->toArray();

        // Sécurité : Vérifier si les tables parentes sont remplies
        if (empty($crops) || empty($countries) || empty($units)) {
            $this->command->error("Erreur : Les tables crops, countries ou units sont vides. Remplis-les d'abord !");
            return;
        }

        $total = 100000;
        $batchSize = 3000; // Nombre de lignes par insertion (Bulk Insert par paquets)
        $data = [];

        $this->command->getOutput()->progressStart($total);

        for ($i = 1; $i <= $total; $i++) {
            // Génération d'un prix cohérent
            $min = rand(300, 450);
            $max = $min + rand(50, 150);
            $average = ($min + $max) / 2;

            $data[] = [
                'id' => Str::uuid(),
                'crop_id' => $crops[array_rand($crops)],
                'country_id' => $countries[array_rand($countries)],
                'state_id' => null, // Optionnel pour le test global
                'city_id' => null,
                'min_price' => $min,
                'max_price' => $max,
                'average_price' => $average,
                'volume_quantity' => rand(50, 5000),
                'unit_id' => $units[array_rand($units)],
                'source_count' => rand(10, 100),
                'recorded_at' => now()->subDays(rand(0, 365))->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 2. Insertion par paquets pour la performance et la RAM
            if (count($data) >= $batchSize) {
                DB::table('price_histories')->insert($data);
                $data = []; // Libère la mémoire vive
                $this->command->getOutput()->progressAdvance($batchSize);
            }
        }

        // Insérer le reste si le total n'est pas un multiple de batchSize
        if (!empty($data)) {
            DB::table('price_histories')->insert($data);
        }

        $this->command->getOutput()->progressFinish();
        $this->command->info("Succès : 100 000 lignes d'historique ajoutées à Upesi !");
    }
}
