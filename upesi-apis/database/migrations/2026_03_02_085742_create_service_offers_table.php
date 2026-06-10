<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // RELATIONS - Correction : merchant_profile_id au lieu de user_id
            $table->foreignUuid('merchant_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('service_id')->constrained('services')->cascadeOnDelete();

            // INFORMATIONS GÉNÉRALES
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('images')->nullable();

            // TARIFICATION
            $table->decimal('price', 12, 2);
            $table->string('price_unit')->default('service'); // service, hectare, km, heure, jour

            // ZONES D'INTERVENTION (pour prestataires et transporteurs)
            $table->json('service_zones')->nullable(); // régions où le service est proposé

            // LOCALISATION (point de départ pour transport, ou lieu pour prestataire)
            $table->string('location_name')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // STATUTS
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected', 'active', 'inactive'])->default('pending');
            $table->text('admin_notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // INDEX
            $table->index('merchant_profile_id');
            $table->index('service_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_offers');
    }
};
