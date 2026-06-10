<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('markets', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Informations de base
            $table->string('name');
            $table->string('slug')->unique();        // 🔵 Index automatique (UNIQUE)
            $table->text('description')->nullable();

            // Médias
            $table->string('image_path')->nullable();
            $table->string('banner_path')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Statuts & Tri
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // ✅ INDEX NÉCESSAIRES (pas créés automatiquement)

            // 1. Recherche et filtrage de base
            $table->index('name');                    // Recherche par nom
            $table->index('is_active');                // Filtrage des marchés actifs
            $table->index('sort_order');               // Tri personnalisé
            $table->index('deleted_at');               // Essentiel pour soft deletes

            // 2. Index composites pour les requêtes fréquentes
            $table->index(['is_active', 'sort_order']); // Marchés actifs triés
            $table->index(['is_active', 'name']);      // Marchés actifs par nom
            $table->index(['deleted_at', 'is_active']); // Soft delete + statut

            // 3. Index pour les tris chronologiques
            $table->index('created_at');                // Tri par date de création
            $table->index('updated_at');                // Tri par date de modification

            // 4. 🔥 FULLTEXT pour moteur de recherche
            $table->fullText(['name', 'description']);  // Recherche plein texte
            $table->fullText('meta_title');             // Recherche dans SEO (optionnel)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('markets');
    }
};
