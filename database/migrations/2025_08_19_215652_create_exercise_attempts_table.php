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
        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exercise_id');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->default(0); // Points earned
            $table->integer('total_points')->default(0); // Points available
            $table->integer('time_spent_seconds')->default(0);
            $table->json('answers')->nullable(); // All user responses
            $table->json('question_results')->nullable(); // Detailed results per question
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['user_id', 'exercise_id', 'completed_at']);
            $table->index(['exercise_id', 'is_completed', 'score']);
            $table->index(['user_id', 'is_completed']);
            
            // Foreign key constraint
            $table->foreign('exercise_id')->references('id')->on('exercises')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
    }
};
