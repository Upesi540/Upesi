<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('transporter_profile_id')->constrained('merchant_profiles')->cascadeOnDelete();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending'); // pending, picked_up, in_transit, delivered, failed
            $table->text('pickup_address');
            $table->text('delivery_address');
            $table->datetime('estimated_pickup_at')->nullable();
            $table->datetime('estimated_delivery_at')->nullable();
            $table->datetime('picked_up_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('transporter_profile_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
