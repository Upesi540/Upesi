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
        Schema::create('slides', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('sub_title')->nullable();

            // Le bouton devient optionnel (nullable)
            $table->string('button_text')->nullable();
            $table->string('link_type')->nullable();
            $table->string('link_url')->nullable();

            // Champs pour la personnalisation des couleurs
            $table->string('button_color')->default('#ff9100')->nullable(); // Vert par défaut (Agriculture)
            $table->string('button_text_color')->default('#ffffff')->nullable(); // Blanc par défaut

            $table->string('image_path');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};
