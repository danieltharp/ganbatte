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
        Schema::create('lessons', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('chapter')->unique();
            $table->string('title_japanese')->nullable();
            $table->string('title_english');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['beginner', 'elementary', 'intermediate', 'advanced'])->default('beginner');
            $table->integer('estimated_time_minutes')->nullable();
            $table->json('prerequisites')->nullable(); // Array of lesson IDs
            $table->timestamps();

            $table->index(['chapter', 'difficulty']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
