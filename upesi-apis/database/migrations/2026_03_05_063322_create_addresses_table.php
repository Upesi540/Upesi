<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relation avec user
            $table->foreignUuid('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Identification de l'adresse
            $table->string('label')->nullable(); // Ex: "Entrepôt principal"
            $table->string('contact_name')->nullable(); // Nom de la personne sur place
            $table->string('contact_phone')->nullable(); // Tel direct pour cette adresse

            // Localisation
            $table->foreignUuid('country_id')->constrained('countries');
            $table->foreignUuid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->nullOnDelete();

            $table->string('prefecture')->nullable();
            $table->string('address_line')->nullable(); // Rue / Quartier / Indications (Portail Vert, etc.)
            $table->string('postal_code')->nullable();

            // GEOLOCALISATION
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Type d’adresse étendu
            $table->enum('type', [
                'shipping',
                'billing',
                'warehouse',
                'pickup',
                'farm',
                'office'
            ])->default('shipping');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true); // Pour désactiver sans supprimer

            $table->timestamps();
            $table->softDeletes(); // Sécurité pour les audits de commandes

            // Index
            $table->index(['user_id', 'type', 'is_active']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
