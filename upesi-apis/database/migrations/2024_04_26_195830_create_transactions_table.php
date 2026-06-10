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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identifiants externes
            $table->string('tid')->nullable()->comment('Transaction ID externe (FedaPay, Stripe, etc.)');
            $table->string('reference')->nullable()->comment('Référence externe ou interne');
            $table->string('gateway', 50)->nullable()->comment('Passerelle: fedapay, stripe, paypal');

            // Montants - Large capacité (20 chiffres dont 8 décimales)
            $table->decimal('amount', 20, 8)->nullable()->comment('Montant de la transaction');
            $table->decimal('fees', 20, 8)->nullable()->comment('Frais de la transaction');
            $table->decimal('amount_transferred', 20, 8)->nullable()->comment('Montant réellement transféré');
            $table->decimal('amount_debited', 20, 8)->nullable()->comment('Montant débité du client');
            $table->decimal('commission', 20, 8)->nullable()->comment('Commission prélevée');
            $table->json('fee_breakdown')->nullable()->comment('Détail des frais: plateforme, partenaire, etc.');

            // Type, statut, opération (string = flexible)
            $table->string('type', 50)->comment('credit ou debit');
            $table->string('status', 50)->default('pending')->comment('pending, completed, approved, transferred, failed, cancelled, refunded, etc.');
            $table->string('operation', 50)->nullable()->comment('deposit, withdrawal, purchase, refund, commission, transfer');

            // Mode de paiement
            $table->string('mode', 50)->nullable()->comment('mobile_money, card, bank_transfer, crypto, cash');

            // Description et metadata
            $table->string('description')->nullable()->comment('Description ou motif');
            $table->text('token')->nullable()->comment('Token de la passerelle paiement');
            $table->json('metadata')->nullable()->comment('Données supplémentaires (IP, user_agent, pays, etc.)');
            $table->json('provider_response')->nullable()->comment('Réponse brute du fournisseur de paiement');

            // Relations
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->nullable()->constrained('wallets')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->nullable()->constrained('currencies')->cascadeOnDelete();

            // Lien vers la transaction wallet (détail des mouvements)
            $table->uuid('wallet_transaction_id')->nullable()->comment('ID dans wallet_transactions');

            // Dates importantes
            $table->timestamp('expire_at')->nullable()->comment('Expiration de la transaction');
            $table->timestamp('declined_at')->nullable()->comment('Date de refus');
            $table->timestamp('canceled_at')->nullable()->comment('Date d\'annulation');
            $table->timestamp('transferred_at')->nullable()->comment('Date du transfert');
            $table->timestamp('refunded_at')->nullable()->comment('Date du remboursement');
            $table->timestamp('processed_at')->nullable()->comment('Date de traitement final');

            // Gestion des erreurs et tentatives
            $table->unsignedTinyInteger('retry_count')->default(0)->comment('Nombre de tentatives');
            $table->text('error_message')->nullable()->comment('Message d\'erreur en cas d\'échec');
            $table->timestamp('last_sync_at')->nullable()->comment('Dernière synchronisation avec le fournisseur');

            // Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();

            // Index pour performance
            $table->index('user_id');
            $table->index('wallet_id');
            $table->index('status');
            $table->index('type');
            $table->index('operation');
            $table->index('gateway');
            $table->index('tid');
            $table->index('reference');
            $table->index('currency_id');
            $table->index('wallet_transaction_id');
            $table->index(['status', 'created_at']); // Pour les requêtes de rattrapage
            $table->index(['gateway', 'status']); // Pour filtrer par passerelle
            $table->index(['user_id', 'created_at']); // Pour l'historique utilisateur
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
