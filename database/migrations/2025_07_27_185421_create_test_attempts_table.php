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
        Schema::create('test_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('test_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('score')->default(0);
            $table->integer('total_points')->default(0);
            $table->integer('time_spent_seconds')->default(0);
            $table->json('answers')->nullable(); // Quick access to all answers
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_passed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'test_id', 'completed_at']);
            $table->index(['test_id', 'is_completed', 'score']);
            $table->index(['user_id', 'is_passed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_attempts');
    }
};
