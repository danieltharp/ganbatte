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
        Schema::create('pages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('lesson_id');
            $table->integer('page_number');
            $table->enum('book_reference', ['textbook', 'workbook']);
            $table->json('content_list')->nullable(); // Array of content items {type, id}
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('lesson_id');
            $table->index('book_reference');
            $table->index(['lesson_id', 'page_number']);
            $table->index(['lesson_id', 'book_reference', 'page_number']);
            $table->unique(['lesson_id', 'page_number', 'book_reference']); // One page per lesson/book/number

            // Foreign key constraint
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
