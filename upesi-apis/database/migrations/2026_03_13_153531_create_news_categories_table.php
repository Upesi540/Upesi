<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // Ex: Météo, Alertes Prix, Transport
            $table->string('slug')->unique();
            $table->string('icon')->nullable(); // Pour stocker heroicon-o-cloud par exemple
            $table->string('color')->nullable(); // Pour stocker une couleur hexadécimale ou une classe (success, info)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Pour ordonner les catégories dans le menu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_categories');
    }
};
