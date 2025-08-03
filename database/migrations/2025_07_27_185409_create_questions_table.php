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
        Schema::create('questions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            
            // Question metadata
            $table->enum('type', [
                'multiple_choice', 'fill_blank', 'translation_j_to_e', 'translation_e_to_j',
                'reading_comprehension', 'listening', 'handwriting', 'sentence_ordering',
                'particle_choice', 'conjugation'
            ]);
            $table->enum('difficulty', ['beginner', 'elementary', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('points')->default(1);
            $table->integer('time_limit_seconds')->nullable();
            
            // Question text in Japanese
            $table->text('question_japanese')->nullable();
            $table->text('question_english')->nullable();
            $table->text('context')->nullable(); // Additional context for the question
            
            // Multimedia
            $table->string('audio_filename')->nullable();
            $table->decimal('audio_duration', 5, 2)->nullable();
            $table->enum('audio_speaker', ['male', 'female', 'child'])->nullable();
            $table->string('image_filename')->nullable();
            $table->json('vocabulary_ids')->nullable();
            $table->json('grammar_ids')->nullable(); // Image filename
            
            // Answer options and correct answer
            $table->json('options')->nullable(); // Array of possible answers
            $table->json('correct_answer'); // Can be string, integer, or array
            
            // Explanation text
            $table->text('explanation_japanese')->nullable();
            $table->text('explanation_english')->nullable();
            
            // Additional help and metadata
            $table->json('hints')->nullable(); // Array of hint strings
            $table->json('tags')->nullable(); // Array of tags
            
            $table->timestamps();

            $table->index(['lesson_id', 'type', 'difficulty']);
            $table->index(['type', 'points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
