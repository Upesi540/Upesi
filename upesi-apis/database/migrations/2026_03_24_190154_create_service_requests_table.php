<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Numéro unique de la demande (ex: SRV-202401-0001)
            $table->string('request_number')->unique();

            // Client (l'acheteur)
            $table->foreignUuid('buyer_id')->constrained('users')->cascadeOnDelete();

            // Le prestataire (transporteur OU prestataire) – toujours un merchant_profile
            $table->foreignUuid('merchant_profile_id')->constrained('merchant_profiles')->cascadeOnDelete();

            // L'offre de service choisie
            $table->foreignUuid('service_offer_id')->constrained('service_offers')->cascadeOnDelete();

            // Statut de la demande
            $table->enum('status', [
                'pending',      // En attente de confirmation du prestataire
                'accepted',     // Accepté par le prestataire
                'rejected',     // Rejeté par le prestataire
                'in_progress',  // En cours d'exécution
                'completed',    // Terminé
                'cancelled'     // Annulé par le client ou le prestataire
            ])->default('pending');

            // Description libre (peut être optionnelle si les détails sont dans le JSON)
            $table->text('description')->nullable();

            // Détails spécifiques au service (pickup/delivery, zone, superficie, etc.)
            $table->json('details')->nullable();

            // Devis et prix final
            $table->decimal('quoted_price', 15, 2)->nullable();
            $table->decimal('final_price', 15, 2)->nullable();

            // Dates
            $table->datetime('scheduled_at')->nullable();      // Date prévue
            $table->datetime('started_at')->nullable();        // Date de début réel
            $table->datetime('completed_at')->nullable();      // Date de fin réelle

            // Annulation
            $table->enum('cancelled_by', ['buyer', 'provider', 'admin'])->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->datetime('cancelled_at')->nullable();

            // Acceptation / Rejet
            $table->datetime('accepted_at')->nullable();
            $table->datetime('rejected_at')->nullable();

            // Devise
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Index pour les recherches
            $table->index('merchant_profile_id');
            $table->index('buyer_id');
            $table->index('status');
            $table->index('service_offer_id');
            $table->index('request_number');
            $table->index(['buyer_id', 'status']);
            $table->index(['merchant_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
