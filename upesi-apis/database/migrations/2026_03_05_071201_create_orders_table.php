<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Numéro de commande unique (ex: UP-2026-0001)
            $table->string('order_number')->unique()->index();

            // --- RELATIONS CŒUR ---
            $table->foreignUuid('buyer_id')->constrained('users')->cascadeOnDelete();

            // --- STATUTS ---
            $table->string('status')->default('pending')->index();
            $table->string('payment_status')->default('pending')->index();

            // Paiement
            $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_reference')->nullable()->index();

            // --- MONTANTS ---
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('tax', 20, 2)->default(0);
            $table->decimal('shipping_cost', 20, 2)->default(0);
            $table->decimal('service_fee', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);

            // Devise
            $table->foreignUuid('currency_id')->constrained('currencies');

            // --- LOGISTIQUE ---
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->foreignUuid('address_id')->nullable()->constrained('addresses')->nullOnDelete();

            // --- MÉTRIQUES ET NOTES ---
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // --- DATES ---
            $table->timestamp('ordered_at')->useCurrent();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // --- ANNULATION ---
            $table->foreignUuid('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancellation_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // --- INDEX ---
            $table->index('ordered_at');
            $table->index(['buyer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
