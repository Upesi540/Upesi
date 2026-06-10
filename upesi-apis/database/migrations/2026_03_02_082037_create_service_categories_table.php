<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();        // 🔵 Index automatique (UNIQUE)
            $table->text('description')->nullable();

            // Visuels pour le site vitrine
            $table->string('icon')->nullable();
            $table->string('banner_path')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // ✅ INDEX NÉCESSAIRES (pas créés automatiquement)

            // 1. Index de base
            $table->index('name');                    // 🔵 Recherche par nom
            $table->index('is_active');                // 🔵 Filtrage catégories actives
            $table->index('sort_order');               // 🔵 Ordre d'affichage personnalisé

            // 2. Index composites pour l'affichage public
            $table->index(['is_active', 'sort_order']); // 🔵 Catégories actives triées (le + important !)
            $table->index(['is_active', 'name']);      // 🔵 Catégories actives par ordre alphabétique

            // 3. Index pour les tris chronologiques (utile pour l'admin)
            $table->index('created_at');                // 🔵 Dernières catégories créées
            $table->index('updated_at');                // 🔵 Dernières modifications

            // 4. 🔥 FULLTEXT pour le moteur de recherche
            $table->fullText(['name', 'description']);  // 🔥 Recherche dans nom + description
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
