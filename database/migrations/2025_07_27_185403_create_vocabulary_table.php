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
        Schema::create('vocabulary', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            
            // Japanese word form
            $table->string('word_japanese')->nullable();
            $table->string('word_furigana')->nullable();
            $table->string('word_english');
            
            // Linguistic information
            $table->enum('part_of_speech', [
                'noun', 'verb', 'adjective', 'adverb', 'particle', 
                'conjunction', 'interjection', 'counter', 'expression'
            ]);
            $table->enum('verb_type', ['ichidan', 'godan', 'irregular', 'suru', 'kuru'])->nullable();
            $table->enum('adjective_type', ['i_adjective', 'na_adjective'])->nullable();
            $table->json('conjugations')->nullable();
            $table->string('pitch_accent')->nullable();
            $table->enum('jlpt_level', ['N5', 'N4', 'N3', 'N2', 'N1'])->nullable();
            $table->integer('frequency_rank')->nullable();
            
            // Additional data
            $table->json('example_sentences')->nullable();
            $table->string('audio_filename')->nullable();
            $table->decimal('audio_duration', 5, 2)->nullable();
            $table->enum('audio_speaker', ['male', 'female', 'child'])->nullable();
            $table->text('mnemonics')->nullable();
            $table->json('related_words')->nullable(); // Array of vocabulary IDs
            $table->json('tags')->nullable();
            $table->boolean('include_in_kanji_worksheet')->default(false);
            
            $table->timestamps();

            $table->index(['lesson_id', 'part_of_speech']);
            $table->index(['jlpt_level', 'frequency_rank']);
            $table->index('word_japanese');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vocabulary');
    }
};
