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
        Schema::create('wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained()->cascadeOnDelete();
            // Type de détenteur : 'user', 'system_commission', 'system_escrow', etc.
            $table->string('holder_type')->default('user');
            $table->decimal('available_balance', 20, 8)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['user_id', 'currency_id']); // 1 wallet par devise
            // Indexation pour la recherche rapide des portefeuilles système
            $table->index(['holder_type', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
