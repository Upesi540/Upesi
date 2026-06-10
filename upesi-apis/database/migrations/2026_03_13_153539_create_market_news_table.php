<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('market_news', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('excerpt')->nullable(); // Résumé/court extrait
            $table->string('featured_image')->nullable(); // Image à la une
            $table->json('meta_data')->nullable(); // Meta description, keywords, etc.
            $table->enum('type', ['flash', 'news', 'article', 'alert'])->default('news');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->foreignUuid('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('news_category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $table->json('tags')->nullable(); // Mots-clés
            $table->dateTime('published_at');
            $table->dateTime('expires_at')->nullable(); // Date d'expiration (optionnel)
            $table->boolean('is_pinned')->default(false); // Rester en haut
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Index pour la recherche et le filtrage
            $table->index('type');
            $table->index('priority');
            $table->index('published_at');
            $table->index('is_pinned');
            $table->index('is_active');
            $table->index(['published_at', 'is_active', 'type']);

            // Fulltext pour recherche avancée
            $table->fullText(['title', 'content', 'excerpt']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('bourse_journals');
    }
};
