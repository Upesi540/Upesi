<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // 'orange_money', 'visa', 'bitcoin'
            $table->string('name'); // 'Orange Money', 'Visa Card', 'Bitcoin'
            $table->string('category'); // 'mobile_money', 'card', 'bank', 'crypto', 'cash', 'other'
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('configuration')->nullable(); // Frais, limites, délais
            $table->json('countries')->nullable(); // Pays où disponible
            $table->json('operators')->nullable(); // Opérateurs supportés (pour mobile money)
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_instant')->default(false); // Paiement instantané ?
            $table->boolean('requires_phone')->default(false); // Nécessite un numéro ?
            $table->boolean('requires_account')->default(false); // Nécessite un compte ?
            $table->json('validation_rules')->nullable(); // Règles de validation
            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('category');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};
