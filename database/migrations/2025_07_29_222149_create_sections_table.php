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
        Schema::create('sections', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('lesson_id');
            $table->integer('page_number');
            $table->enum('page_section', ['full', 'top', 'middle', 'bottom', 'left', 'right', 'center'])->default('full');
            $table->enum('section_type', [
                'dialogue', 'vocabulary_intro', 'grammar_intro', 'listening', 
                'pronunciation', 'cultural_note', 'review', 'practice'
            ]);
            $table->integer('order_weight')->default(0);
            $table->text('purpose')->nullable();
            $table->text('instructions')->nullable();
            $table->string('audio_filename')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->json('prerequisites')->nullable(); // Array of section IDs
            $table->json('related_vocabulary_ids')->nullable(); // Array of vocabulary IDs
            $table->json('related_grammar_ids')->nullable(); // Array of grammar IDs
            $table->boolean('completion_trackable')->default(true);
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('lesson_id');
            $table->index('section_type');
            $table->index(['page_number', 'order_weight']);
            $table->index(['lesson_id', 'page_number', 'order_weight']);
            $table->index('completion_trackable');

            // Foreign key constraint
            $table->foreign('lesson_id')->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
