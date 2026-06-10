<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitsSeeder extends Seeder
{
    private $totalUnits = 52; // Augmenté pour inclure les nouvelles unités

    public function run(): void
    {
        // Désactiver les événements Eloquent pour la performance
        Unit::unsetEventDispatcher();

        $this->command->info('⏳ Création des unités de mesure...');
        $this->command->getOutput()->progressStart($this->totalUnits);

        $units = [
            // 📦 UNITÉS DE CONDITIONNEMENT (AVEC CASIER)
            ['name' => 'Casier', 'symbol' => 'casier', 'description' => 'Casier de fruits/légumes (souvent 10-15kg)'],
            ['name' => 'Demi-casier', 'symbol' => '1/2 casier', 'description' => 'Demi-casier de fruits/légumes'],
            ['name' => 'Cagette', 'symbol' => 'cagette', 'description' => 'Petite cagette en bois ou plastique'],
            ['name' => 'Caisse', 'symbol' => 'caisse', 'description' => 'Caisse de fruits/légumes'],
            ['name' => 'Carton', 'symbol' => 'carton', 'description' => 'Carton standard'],
            ['name' => 'Sac', 'symbol' => 'sac', 'description' => 'Sac standard (souvent 50kg ou 100kg)'],
            ['name' => 'Palette', 'symbol' => 'pal', 'description' => 'Palette complète'],
            ['name' => 'Botte', 'symbol' => 'botte', 'description' => 'Botte de céréales/fourrage/légumes'],
            ['name' => 'Balle', 'symbol' => 'balle', 'description' => 'Balle de foin/paille'],
            ['name' => 'Régime', 'symbol' => 'régime', 'description' => 'Régime de bananes/dattes'],
            ['name' => 'Grappe', 'symbol' => 'grappe', 'description' => 'Grappe de raisin/tomates'],
            ['name' => 'Filet', 'symbol' => 'filet', 'description' => 'Filet d\'agrumes/oignons/pommes de terre'],
            ['name' => 'Sachet', 'symbol' => 'sachet', 'description' => 'Sachet pour petites quantités'],
            ['name' => 'Barquette', 'symbol' => 'barq', 'description' => 'Barquette de fruits rouges'],

            // 📦 AUTRES UNITÉS DE CONDITIONNEMENT SPÉCIFIQUES
            ['name' => 'Panier', 'symbol' => 'panier', 'description' => 'Panier traditionnel'],
            ['name' => 'Corbeille', 'symbol' => 'corbeille', 'description' => 'Corbeille de présentation'],
            ['name' => 'Plateau', 'symbol' => 'plateau', 'description' => 'Plateau de fruits/légumes'],
            ['name' => 'Clayette', 'symbol' => 'clayette', 'description' => 'Clayette pour œufs ou petits fruits'],

            // 📦 UNITÉS DE POIDS
            ['name' => 'Kilogramme', 'symbol' => 'kg', 'description' => 'Unité de poids standard (1000 grammes)'],
            ['name' => 'Gramme', 'symbol' => 'g', 'description' => 'Unité de poids pour petites quantités'],
            ['name' => 'Tonne', 'symbol' => 't', 'description' => '1000 kilogrammes, pour gros volumes'],
            ['name' => 'Livre', 'symbol' => 'lb', 'description' => 'Unité anglo-saxonne (0.453 kg)'],
            ['name' => 'Demi-kilogramme', 'symbol' => '500g', 'description' => '500 grammes'],
            ['name' => 'Quart de kilogramme', 'symbol' => '250g', 'description' => '250 grammes'],

            // 💧 UNITÉS DE VOLUME
            ['name' => 'Litre', 'symbol' => 'L', 'description' => 'Unité de volume standard'],
            ['name' => 'Millilitre', 'symbol' => 'mL', 'description' => 'Unité de volume pour petites quantités'],
            ['name' => 'Centilitre', 'symbol' => 'cL', 'description' => 'Unité de volume courante pour boissons'],
            ['name' => 'Mètre cube', 'symbol' => 'm³', 'description' => 'Unité de volume pour liquides en vrac'],
            ['name' => 'Gallon', 'symbol' => 'gal', 'description' => 'Unité anglo-saxonne (3.785 L)'],
            ['name' => 'Demi-litre', 'symbol' => '500mL', 'description' => '500 millilitres'],

            // 📏 UNITÉS DE LONGUEUR
            ['name' => 'Mètre', 'symbol' => 'm', 'description' => 'Unité de longueur standard'],
            ['name' => 'Centimètre', 'symbol' => 'cm', 'description' => 'Unité de longueur pour petites dimensions'],

            // 🔢 UNITÉS DE COMPTAGE
            ['name' => 'Unité', 'symbol' => 'u', 'description' => 'Pièce individuelle'],
            ['name' => 'Pièce', 'symbol' => 'pc', 'description' => 'À la pièce'],
            ['name' => 'Douzaine', 'symbol' => 'dz', 'description' => '12 unités'],
            ['name' => 'Demi-douzaine', 'symbol' => '6', 'description' => '6 unités'],
            ['name' => 'Centaine', 'symbol' => '100', 'description' => '100 unités'],
            ['name' => 'Millier', 'symbol' => '1000', 'description' => '1000 unités'],
            ['name' => 'Lot', 'symbol' => 'lot', 'description' => 'Lot de plusieurs articles'],
            ['name' => 'Paquet', 'symbol' => 'pqt', 'description' => 'Paquet standard'],

            // 📊 UNITÉS AGRICOLES SPÉCIFIQUES
            ['name' => 'Hectare', 'symbol' => 'ha', 'description' => 'Surface agricole (10 000 m²)'],
            ['name' => 'Acre', 'symbol' => 'acre', 'description' => 'Unité de surface anglo-saxonne (0.4047 ha)'],

            // 🥚 UNITÉS SPÉCIFIQUES PRODUITS
            ['name' => 'Œuf', 'symbol' => 'œuf', 'description' => 'Pièce pour les œufs'],
            ['name' => 'Plaquette', 'symbol' => 'plaquette', 'description' => 'Plaquette de beurre'],
            ['name' => 'Bouteille', 'symbol' => 'btl', 'description' => 'Bouteille standard'],
            ['name' => 'Canette', 'symbol' => 'can', 'description' => 'Canette 33cL'],
            ['name' => 'Bidon', 'symbol' => 'bidon', 'description' => 'Bidon de lait/huile'],
            ['name' => 'Fût', 'symbol' => 'fût', 'description' => 'Fût de 200L'],

            // 🌾 UNITÉS TRADITIONNELLES AFRICAINES
            ['name' => 'Bassin', 'symbol' => 'bassin', 'description' => 'Unité traditionnelle (environ 20kg)'],
            ['name' => 'Cuvette', 'symbol' => 'cuvette', 'description' => 'Unité traditionnelle de mesure'],
            ['name' => 'Seau', 'symbol' => 'seau', 'description' => 'Seau standard (environ 15L)'],
            ['name' => 'Tine', 'symbol' => 'tine', 'description' => 'Grande bassine (environ 50kg)'],
            ['name' => 'Gourde', 'symbol' => 'gourde', 'description' => 'Unité traditionnelle pour liquides'],
            ['name' => 'Bol', 'symbol' => 'bol', 'description' => 'Unité traditionnelle pour petites quantités'],
            ['name' => 'Calebasse', 'symbol' => 'calebasse', 'description' => 'Unité traditionnelle'],

            // 📦 UNITÉS POUR GROS VOLUMES (Bourse agricole)
            ['name' => 'Conteneur 20 pieds', 'symbol' => '20ft', 'description' => 'Conteneur 20 pieds'],
            ['name' => 'Conteneur 40 pieds', 'symbol' => '40ft', 'description' => 'Conteneur 40 pieds'],
            ['name' => 'Camion', 'symbol' => 'camion', 'description' => 'Camion complet'],
            ['name' => 'Benne', 'symbol' => 'benne', 'description' => 'Benne agricole'],
            ['name' => 'Remorque', 'symbol' => 'remorque', 'description' => 'Remorque agricole'],
            ['name' => 'Wagon', 'symbol' => 'wagon', 'description' => 'Wagon de train'],
        ];

        // Remplacez la section "Insertion en masse" par ceci :

        // Préparer les données avec les IDs UUID
        $this->command->getOutput()->progressStart(count($units));

        foreach ($units as $unit) {
            Unit::firstOrCreate(
                ['name' => $unit['name'], 'symbol' => $unit['symbol']], // critères de recherche
                [ // données à insérer SEULEMENT si non trouvé
                    'id' => Str::uuid(),
                    'description' => $unit['description'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->getOutput()->progressAdvance();
        }


        // Statistiques
        $total = Unit::count();
        $this->command->info("✅ {$total} unités de mesure en base de données");

        // upsert met à jour si la combinaison name+symbol existe, sinon insère

        $this->command->getOutput()->progressFinish();

        // Statistiques par catégorie pour le feedback
        $total = count($units);
        $conditionnement = count(array_filter(
            $units,
            fn($u) =>
            in_array($u['symbol'], ['casier', 'cagette', 'caisse', 'carton', 'sac', 'pal', 'botte', 'filet'])
        ));

        $this->command->info("✅ {$total} unités de mesure créées avec succès !");
        $this->command->line("   📦 Conditionnement: {$conditionnement} unités (dont casier)");
        $this->command->line("   ⚖️  Poids/Volume: " . count(array_filter(
            $units,
            fn($u) =>
            in_array($u['symbol'], ['kg', 'g', 't', 'L', 'mL'])
        )) . " unités");
        $this->command->line("   🔢 Comptage: " . count(array_filter(
            $units,
            fn($u) =>
            in_array($u['symbol'], ['u', 'pc', 'dz', 'lot'])
        )) . " unités");
        $this->command->line("   🌍 Traditionnelles: " . count(array_filter(
            $units,
            fn($u) =>
            in_array($u['symbol'], ['bassin', 'cuvette', 'seau', 'tine', 'calebasse'])
        )) . " unités");
    }
}
