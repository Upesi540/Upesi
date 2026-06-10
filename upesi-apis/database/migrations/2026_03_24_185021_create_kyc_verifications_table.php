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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // 1. TYPE DE DOSSIER : Individu ou Entreprise
            $table->enum('entity_type', ['individual', 'business'])->default('individual');

            // 2. IDENTITÉ (Individu ou Gérant)
            $table->string('document_type'); // CNI, Passeport, Carte de planteur
            $table->string('document_number');
            $table->json('document_files'); // Photos recto/verso
            $table->string('selfie_path')->nullable();

            // 3. CONFORMITÉ BUSINESS (Dossier Gabonais)
            $table->string('rccm_number')->nullable()->unique(); // Registre du Commerce
            $table->string('rccm_path')->nullable();
            $table->string('cfe_card_path')->nullable(); // Carte CFE (ANPI)
            $table->string('quitus_fiscal_path')->nullable(); // Quitus de la DGI
            $table->string('nif_number')->nullable(); // Numéro d'Identification Fiscale

            // 4. INFOS MAGASIN & GÉOLOCALISATION
            $table->text('store_description')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('google_maps_url')->nullable();

            // 5. ÉTAT DE VALIDATION
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->text('admin_notes')->nullable();

            // 6. DATES CLÉS ET TIMESTAMPS
            $table->timestamp('verified_at')->nullable();
            $table->date('expiry_date')->nullable(); // Expiration de la pièce ou du quitus
            $table->timestamps();
            $table->softDeletes();

            // INDEXATION POUR LES PERFORMANCES
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
