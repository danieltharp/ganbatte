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
        Schema::create('articles', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('lesson_id');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->json('covered_vocabulary_ids')->nullable(); // Array of vocabulary IDs covered in this article
            $table->timestamps();

            // Foreign key constraint - lesson_id references lessons.id (string)
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
            
            // Indexes
            $table->index(['lesson_id']);
            $table->index(['title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
