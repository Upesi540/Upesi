<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Relations
            $table->foreignUuid('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained();

            // Identifiants de transaction
            $table->string('reference')->unique(); // Référence unique (ex: TX-2024-001)

            // Type de transaction limité à ces valeurs
            $table->enum('type', ['credit', 'debit', 'freeze', 'unfreeze']);

            // Montants
            $table->decimal('amount', 15, 4);
            $table->decimal('balance_before', 15, 4);
            $table->decimal('balance_after', 15, 4);

            // Contexte
            $table->string('operation_type'); // achat, vente, depot, retrait, commission, etc.
            $table->uuidMorphs('transactionable', 'wallet_txn_trxble_idx');
            // Métadonnées
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Infos supplémentaires

            // Statut limité à ces valeurs
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('completed');

            $table->timestamps();
            $table->index(['wallet_id', 'created_at']);
            $table->index('reference');
            $table->index(['wallet_id', 'type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
