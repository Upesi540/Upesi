<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('gallery')->nullable(); // JSON pour plusieurs images
            $table->string('client')->nullable(); // Nom du client
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('completed'); // planned, ongoing, completed
            $table->string('location')->nullable();
            $table->json('testimonials')->nullable(); // Témoignages clients
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('is_active');
            $table->index('start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
