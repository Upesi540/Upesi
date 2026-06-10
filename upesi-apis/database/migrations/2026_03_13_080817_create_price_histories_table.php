<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // 1. RELATIONS CŒUR
            $table->foreignUuid('crop_id')->constrained('crops')->cascadeOnDelete();

            // 2. GÉOGRAPHIE (C'est ici que se joue la bourse par pays/région)
            $table->foreignUuid('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignUuid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->nullOnDelete();

            // 3. INDICATEURS DE PRIX
            $table->decimal('min_price', 15, 2);
            $table->decimal('max_price', 15, 2);
            $table->decimal('average_price', 15, 2); // Ta Médiane (Le "Prix Bourse")

            // 4. VOLUME, UNITÉ ET FIABILITÉ
            $table->decimal('volume_quantity', 15, 2)->default(0);
            $table->foreignUuid('unit_id')->constrained('units');
            $table->integer('source_count')->default(0); // <-- LE PETIT PLUS : nombre d'offres traitées

            // 5. TEMPS
            $table->date('recorded_at'); // Date du relevé (Ex: 2026-03-18)
            $table->timestamps();

            // 6. INDEX DE PERFORMANCE (Vital pour les tendances et graphiques)
            // Permet de trouver instantanément le dernier prix d'un produit dans un pays précis
            $table->index(['crop_id', 'country_id', 'recorded_at'], 'idx_crop_country_date');
            $table->index(['crop_id', 'state_id', 'recorded_at'], 'idx_crop_state_date');
            $table->index(['crop_id', 'city_id', 'recorded_at'], 'idx_crop_city_date');

            // CAS 2 : Recherche globale par pays (Home Page : "Voir tout le Togo")
            // Très important quand l'utilisateur ne filtre pas encore par produit
            $table->index(['country_id', 'recorded_at'], 'idx_country_date');

            // CAS 3 : Recherche par date seule (Admin / Statistiques mensuelles)
            // Déjà géré par ->index() sur la colonne recorded_at, mais plus propre ici
            $table->index('recorded_at', 'idx_recorded_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('price_histories');
    }
};
