<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_category_id')
                ->constrained()
                ->cascadeOnDelete();                    // 🔵 Index automatique (foreign key)

            // INFORMATIONS DE BASE
            $table->string('name');
            $table->string('slug')->unique();           // 🔵 Index automatique (UNIQUE)
            $table->text('description')->nullable();

            // MÉDIAS & STYLE
            $table->string('icon')->nullable();
            $table->string('image_path')->nullable();

            // SEO (Indispensable pour la visibilité d'UPESI)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();  // Note: JSON ne peut pas être indexé directement

            // STATUTS & TRI
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // ✅ INDEX NÉCESSAIRES (pas créés automatiquement)

            // 1. Index de base pour recherche et filtrage
            $table->index('name');                         // 🔵 Recherche par nom
            $table->index('is_active');                     // 🔵 Filtrage services actifs
            $table->index('sort_order');                    // 🔵 Ordre d'affichage
            $table->index('deleted_at');                    // 🔵 Soft deletes

            // 2. Index composites CRUCIAUX pour les requêtes fréquentes
            $table->index(['service_category_id', 'is_active', 'sort_order']);
            // 🎯 Affichage des services actifs d'une catégorie dans l'ordre (requête PRINCIPALE)

            $table->index(['service_category_id', 'is_active', 'name']);
            // 🔵 Liste alphabétique des services actifs par catégorie

            $table->index(['is_active', 'sort_order']);
            // 🔵 Tous les services actifs triés (moins fréquent mais utile)

            $table->index(['service_category_id', 'deleted_at']);
            // 🗑️ Gestion de la corbeille par catégorie

            // 3. Index pour les tris chronologiques
            $table->index('created_at');                    // 🔵 Nouveaux services
            $table->index('updated_at');                    // 🔵 Dernières modifications

            // 4. 🔥 FULLTEXT pour le moteur de recherche avancé
            $table->fullText(['name', 'description']);       // 🔥 Recherche principale
            $table->fullText('meta_title');                  // 🔥 Recherche SEO (optionnel)

            // 5. Note pour meta_keywords :
            // Comme c'est un champ JSON, pour indexer tu devras créer des colonnes virtuelles
            // Mais ça peut venir plus tard si besoin
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
