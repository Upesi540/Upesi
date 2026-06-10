<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique(); // Pour des URLs propres

            // Visuels
            $table->string('logo_path')->nullable();
            $table->string('cover_image')->nullable(); // Optionnel : pour une page de profil partenaire

            // Liens & Réseaux
            $table->string('website_url')->nullable();
            $table->string('facebook_url')->nullable(); // Très important pour le marché gabonais

            // Classification
            $table->enum('type', ['technical', 'financial', 'institutional', 'media', 'commercial', 'other'])->default('other');
            $table->string('level')->default('standard'); // ex: gold, silver, standard

            // Contenu
            $table->text('description')->nullable();
            $table->string('short_description', 155)->nullable(); // Pour les balises Meta SEO

            // Contact Interne (non affiché sur le site)
            $table->string('internal_contact_name')->nullable();
            $table->string('internal_contact_email')->nullable();

            // Stats & Tri
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_home')->default(false); // Pour les mettre en avant sur la landing page

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
