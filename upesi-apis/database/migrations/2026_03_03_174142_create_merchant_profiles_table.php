<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('merchant_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('shop_name')->index();
            $table->string('logo_path')->nullable();
            $table->string('phone')->nullable();
            $table->text('description')->nullable();

            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();

            // Le type métier
            $table->string('type')->index();

            // ✅ Contrainte d'unicité : un user ne peut pas avoir deux fois le même type
            $table->unique(['user_id', 'type'], 'merchant_profiles_user_type_unique');

            // Le statut pour la validation admin
            $table->string('status')->default('pending')->index();

            // Le champ JSON pour les données spécifiques
            $table->json('metadata')->nullable();

            $table->foreignUuid('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('merchant_profiles');
    }
};
