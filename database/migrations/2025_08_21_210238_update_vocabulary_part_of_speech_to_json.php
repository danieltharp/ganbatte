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
        Schema::table('vocabulary', function (Blueprint $table) {
            // Change the part_of_speech column to be a json column
            $table->json('part_of_speech')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocabulary', function (Blueprint $table) {
            $table->enum('part_of_speech', [
                'noun', 'verb', 'adjective', 'adverb', 'particle', 
                'conjunction', 'interjection', 'counter', 'expression',
                'affix', 'kanji'
            ])->nullable()->change();
        });
    }
};
