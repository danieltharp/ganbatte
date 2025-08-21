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
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->string('lesson_id'); // e.g., 'mnn-lesson-01'
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('object_type'); // 'vocabulary', 'grammar_point', 'lesson', etc.
            $table->string('object_id'); // The ID of the specific object being contributed to
            $table->string('field_type')->nullable(); // 'mnemonic', 'example_sentence', 'pronunciation', etc.
            $table->text('contribution_text'); // The actual contribution content
            $table->string('status')->default('new'); // 'new', 'accepted', 'completed'
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['lesson_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['object_type', 'object_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contributions');
    }
};
