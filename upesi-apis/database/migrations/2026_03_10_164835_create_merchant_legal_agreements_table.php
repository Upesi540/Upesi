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
        Schema::create('merchant_legal_agreements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('merchant_profile_id')->constrained('merchant_profiles')->onDelete('cascade');
            $table->foreignUuid('legal_document_id')->nullable()->constrained('legal_documents')->restrictOnDelete();
            $table->string('agreement_type');
            $table->string('version');
            $table->text('user_agent')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('accepted_at');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_legal_agreements');
    }
};
