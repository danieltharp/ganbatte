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
        Schema::create('exercises', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('lesson_id');
            $table->integer('page_number');
            $table->enum('book_reference', ['textbook', 'workbook']);
            $table->integer('order_weight')->default(0);
            $table->text('overview')->nullable();
            $table->json('question_ids')->nullable(); // Array of question IDs
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('lesson_id');
            $table->index('book_reference');
            $table->index(['page_number', 'order_weight']);
            $table->index(['lesson_id', 'page_number', 'order_weight']);

            // Foreign key constraint
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
