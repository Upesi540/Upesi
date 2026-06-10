<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Kilogramme, Litre, Unité, etc.
            $table->string('symbol'); // kg, L, u, etc.
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Index pour la recherche
            $table->index('name');
            $table->index('symbol');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('units');
    }
};
