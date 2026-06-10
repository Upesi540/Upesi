<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relations
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();

            // 👇 CECI EST LA CLÉ POUR LE MULTI-VENDEURS
            $table->foreignUuid('merchant_profile_id')
                ->nullable()
                ->constrained('merchant_profiles')
                ->nullOnDelete();

            // Stats par vendeur
            $table->string('seller_status')->default('pending');
            $table->timestamp('seller_confirmed_at')->nullable();
            $table->timestamp('seller_shipped_at')->nullable();
            $table->timestamp('seller_delivered_at')->nullable();
            $table->timestamp('seller_paid_at')->nullable();

            // Tracking par vendeur
            $table->string('tracking_number')->nullable();
            $table->string('shipping_carrier')->nullable();

            // Informations produit (snapshot)
            $table->string('product_name');
            $table->decimal('quantity', 10, 2);
            $table->foreignUuid('unit_id')->constrained('units');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Commission par vendeur
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('seller_gets', 10, 2)->default(0);

            // 👇 AJOUTER CES DEUX COLONNES POUR L'ANNULATION
            $table->string('cancelled_by')->nullable(); // 'buyer', 'seller', 'admin'
            $table->text('cancellation_reason')->nullable();

            // Autres
            $table->json('metadata')->nullable();
            $table->boolean('is_custom_item')->default(false);
            $table->text('custom_description')->nullable();

            $table->timestamps();

            // Index
            $table->index('order_id');
            $table->index('product_id');
            $table->index('unit_id');
            $table->index('merchant_profile_id');
            $table->index('seller_status');
            $table->index('cancelled_by');
            $table->index(['order_id', 'merchant_profile_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
