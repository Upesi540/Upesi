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
        Schema::create('currencies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 10)->unique(); // USD, EUR, XOF, XOF
            $table->string('name'); // Dollar, Euro, Franc CFA
            $table->string('symbol', 10); // $, €, FCFA
            $table->decimal('exchange_rate', 10, 4)->default(1); // Taux par rapport à la devise de base
            $table->boolean('is_base')->default(false); // Une seule devise de base
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('precision')->default(2); // 8 pour BTC, 0 pour XOF
            $table->boolean('is_crypto')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
