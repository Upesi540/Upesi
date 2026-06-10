<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('variety')->nullable();
            $table->string('grade')->nullable(); // Ex: Grade A, Grade 1, Export Quality

            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignUuid('default_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Image de référence (Admin)

            // Standards de qualité pour cette bourse (ex: taux d'humidité, pureté)
            $table->json('quality_standards')->nullable();

            $table->string('scientific_name')->nullable();
            $table->json('growing_seasons')->nullable();
            $table->integer('growing_days')->nullable();
            $table->json('attributes')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name', 'variety', 'grade'], 'unique_crop_specification');
            // Index
            $table->index(['name', 'grade', 'variety']); // Index composé pour recherche rapide
            $table->index('is_active');
            $table->fullText(['name', 'scientific_name', 'description', 'variety']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('crops');
    }
};
