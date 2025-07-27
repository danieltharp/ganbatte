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
        Schema::create('worksheets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->enum('type', [
                'kanji_practice', 'hiragana_practice', 'katakana_practice',
                'vocabulary_review', 'grammar_exercises', 'reading_comprehension'
            ]);
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->json('content_ids')->nullable();
            $table->string('template')->nullable(); // Template name for generation
            $table->json('print_settings')->nullable(); // Paper size, margins, etc.
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['lesson_id', 'type']);
            $table->index(['type', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheets');
    }
};
