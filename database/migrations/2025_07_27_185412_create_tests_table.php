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
        Schema::create('tests', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('lesson_ids')->nullable();
            $table->json('question_ids')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->integer('passing_score')->default(70); // Percentage
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_options')->default(true);
            $table->boolean('allow_retakes')->default(true);
            $table->boolean('show_results_immediately')->default(false);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'passing_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
