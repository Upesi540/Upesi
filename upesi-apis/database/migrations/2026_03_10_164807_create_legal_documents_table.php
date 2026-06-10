<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // Migration : create_legal_documents_table
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title'); // ex: "Conditions Générales d'Utilisation"
            $table->string('slug');  // ex: "cgu" ou "privacy-policy"
            $table->longText('content'); // Le texte complet en HTML
            $table->string('version')->default('1.0'); // Très important pour l'audit
            $table->boolean('is_active')->default(true); // Pour désactiver une ancienne version
            $table->timestamps();

            $table->unique(['slug', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
