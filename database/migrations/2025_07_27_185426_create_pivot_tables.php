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
        // Test-Lesson relationship
        Schema::create('test_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['test_id', 'lesson_id']);
        });

        // Test-Question relationship
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0); // Order of questions in test
            $table->decimal('weight', 3, 2)->default(1.0); // Question weight/importance
            $table->timestamps();

            $table->unique(['test_id', 'question_id']);
            $table->index(['test_id', 'order']);
        });

        // Question-Vocabulary relationship
        Schema::create('question_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('vocabulary_id')->constrained('vocabulary')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['question_id', 'vocabulary_id']);
        });

        // Question-Grammar relationship
        Schema::create('question_grammar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('grammar_point_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['question_id', 'grammar_point_id']);
        });

        // Worksheet-Vocabulary relationship
        Schema::create('worksheet_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('vocabulary_id')->constrained('vocabulary')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['worksheet_id', 'vocabulary_id']);
            $table->index(['worksheet_id', 'order']);
        });

        // Worksheet-Grammar relationship
        Schema::create('worksheet_grammar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('grammar_point_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['worksheet_id', 'grammar_point_id']);
            $table->index(['worksheet_id', 'order']);
        });

        // Worksheet-Question relationship (for exercise worksheets)
        Schema::create('worksheet_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worksheet_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['worksheet_id', 'question_id']);
            $table->index(['worksheet_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worksheet_questions');
        Schema::dropIfExists('worksheet_grammar');
        Schema::dropIfExists('worksheet_vocabulary');
        Schema::dropIfExists('question_grammar');
        Schema::dropIfExists('question_vocabulary');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('test_lessons');
    }
};
