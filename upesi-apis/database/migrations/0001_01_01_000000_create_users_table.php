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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignUuId('preferred_currency_id')->nullable()->constrained('currencies');

            $table->string('last_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('email')->nullable()->unique();
            $table->string('prefecture')->nullable();
            // Dans la table users, ajoute cette ligne après `deleted_by` par exemple :

            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->foreignUuid('current_team_id')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->boolean('is_active')->default(true); // Bloquer l'accès global
            $table->boolean('is_banned')->default(false); // Bannissement

            // 👇 AJOUT POUR SOFT DELETE
            $table->softDeletes(); // Ajoute deleted_at (null = actif, non-null = supprimé)

            // Raison de la suppression/bannissement
            $table->text('deletion_reason')->nullable();
            $table->foreignUuid('deleted_by')->nullable()->constrained('users');
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
            $table->boolean('has_email_authentication')->default(false);

            $table->timestamps();

            // ✅ AJOUT DES INDEX MANQUANTS
            $table->index('last_name');      // Recherches par nom de famille
            $table->index('first_name');     // Recherches par prénom
            $table->index('is_active');      // Filtrage des utilisateurs actifs
            $table->index('is_banned');      // Filtrage des utilisateurs bannis
            $table->index('deleted_at');     // Essentiel pour les soft deletes
            $table->index(['is_active', 'is_banned']); // Combinaison courante
            $table->index('created_at');
            $table->index('created_by');
            $table->index('country_id');
            $table->index('preferred_currency_id');
            // Index composite pour les recherches fréquentes
            $table->index(['last_name', 'first_name']); // Recherche nom + prénom

            // Si tu fais souvent des recherches par statut + date
            $table->index(['deleted_at', 'created_at']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
