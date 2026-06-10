<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // --- RELATIONS CŒUR ---
            $table->foreignUuid('merchant_profile_id')
                ->constrained('merchant_profiles')
                ->cascadeOnDelete();

            $table->foreignUuid('crop_id')->constrained('crops')->cascadeOnDelete();

            $table->string('title')->nullable();
            $table->string('sku')->unique();
            $table->text('description')->nullable();

            // --- PRIX ET QUANTITÉ ---
            $table->decimal('quantity', 15, 2);
            $table->decimal('min_order_quantity', 15, 2)->default(1);

            $table->decimal('unit_price', 15, 2);

            // MODIFICATION ICI : On utilise la table currencies
            $table->foreignUuid('currency_id')->constrained('currencies');

            $table->foreignUuid('unit_id')->constrained('units');

            // --- GÉOGRAPHIE ---
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignUuid('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->foreignUuid('country_id')->nullable()->constrained('countries')->nullOnDelete();

            $table->string('warehouse_name')->nullable();
            $table->string('address')->nullable();

            // GEOLOCALISATION
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // --- INFOS ET ÉTAT ---
            $table->json('harvest_info')->nullable();
            $table->json('images')->nullable();
            $table->enum('status', ['draft', 'active', 'sold', 'expired', 'inactive'])->default('active');

            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // --- INDEX ---
            $table->index(['merchant_profile_id', 'status']);
            $table->index('crop_id');
            $table->index('currency_id'); // Ajout d'un index pour filtrer par devise si besoin
            $table->index(['state_id', 'city_id']);
            $table->index('unit_price');
            $table->fullText(['title', 'description']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
