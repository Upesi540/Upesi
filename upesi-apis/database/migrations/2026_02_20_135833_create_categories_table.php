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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // 1. Hierarchy: Allows a category to be a "child" of another
            $table->foreignUuid('parent_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->foreignUuid('market_id')->constrained('markets')->cascadeOnDelete(); // 🔵 Index automatique (foreign key)

            // 2. Visuals
            $table->string('icon')->nullable(); // Example: 'heroicon-o-cpu'
            $table->string('image_path')->nullable(); // Banner image

            // 3. Organization & SEO
            $table->integer('sort_order')->default(0); // To define display priority
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Ajoute la colonne deleted_at
            // ✅ AJOUT DES INDEX NÉCESSAIRES
            $table->index('sort_order');              // Tri des catégories
            $table->index('is_active');                // Filtrage des catégories actives
            $table->index('deleted_at');               // Essentiel pour soft deletes

            // Migration
            $table->fullText(['name', 'description']); // Index fulltext sur 2 colonnes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
