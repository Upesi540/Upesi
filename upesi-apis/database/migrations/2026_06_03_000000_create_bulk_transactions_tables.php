<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table principale des transactions groupées
        // 1. Table principale des transactions groupées
        Schema::create('bulk_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('trader_id')->constrained('users');  // ← CHANGÉ negociant_id → trader_id
            $table->enum('type', ['sale', 'purchase']);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'completed'])->default('draft');
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->decimal('trader_commission', 10, 2)->default(0);  // ← CHANGÉ negociant_commission → trader_commission
            $table->foreignUuid('counterparty_id')->nullable()->constrained('users');
            $table->foreignUuid('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trader_id', 'status']);
            $table->index(['type', 'status']);
        });

        // 2. Table des détails (participants et produits)
        Schema::create('bulk_transaction_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bulk_transaction_id')->constrained('bulk_transactions')->cascadeOnDelete();
            $table->foreignUuid('merchant_profile_id')->constrained('merchant_profiles');
            $table->enum('participant_type', ['seller', 'buyer']);
            $table->string('product_name');
            $table->decimal('quantity', 20, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 20, 2);
            $table->decimal('subtotal', 20, 2);
            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->decimal('participant_gets', 20, 2);
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('bulk_transaction_id');
            $table->index('merchant_profile_id');
            $table->index('participant_type');
        });

        // 3. Table de liaison avec les commandes
        Schema::create('bulk_transaction_orders', function (Blueprint $table) {
            $table->foreignUuid('bulk_transaction_id')->constrained('bulk_transactions')->cascadeOnDelete();
            $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
            $table->primary(['bulk_transaction_id', 'order_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_transaction_orders');
        Schema::dropIfExists('bulk_transaction_details');
        Schema::dropIfExists('bulk_transactions');
    }
};
