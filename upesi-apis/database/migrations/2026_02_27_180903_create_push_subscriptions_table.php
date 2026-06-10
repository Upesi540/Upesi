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
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->text('token'); // Le token FCM
            $table->string('platform'); // 'android', 'ios', 'web'
            $table->string('device_name')->nullable(); // ex: 'iPhone 15', 'Chrome Win10'
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable(); // Pour le nettoyage des tokens morts
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
