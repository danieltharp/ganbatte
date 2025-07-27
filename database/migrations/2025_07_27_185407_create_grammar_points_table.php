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
        Schema::create('grammar_points', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            
            // Grammar point name
            $table->string('name_japanese')->nullable();
            $table->string('name_english');
            
            // Grammar information
            $table->string('pattern'); // e.g., "X は Y です"
            $table->text('usage'); // When and how to use this grammar
            $table->text('explanation'); // Detailed explanation
            $table->enum('jlpt_level', ['N5', 'N4', 'N3', 'N2', 'N1'])->nullable();
            
            // Examples and relationships
            $table->json('examples')->nullable(); // Array of example objects
            $table->json('related_grammar')->nullable(); // Array of related grammar IDs
            
            $table->timestamps();

            $table->index(['lesson_id', 'jlpt_level']);
            $table->index('pattern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grammar_points');
    }
};
