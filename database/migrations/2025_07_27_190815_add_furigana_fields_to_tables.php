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
        // Add furigana field to lessons table
        Schema::table('lessons', function (Blueprint $table) {
            $table->text('title_furigana')->nullable()->after('title_japanese');
        });

        // Add furigana field to grammar_points table
        Schema::table('grammar_points', function (Blueprint $table) {
            $table->text('name_furigana')->nullable()->after('name_japanese');
        });

        // Add furigana fields to questions table
        Schema::table('questions', function (Blueprint $table) {
            $table->text('question_furigana')->nullable()->after('question_japanese');
            $table->text('explanation_furigana')->nullable()->after('explanation_japanese');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('title_furigana');
        });

        Schema::table('vocabulary', function (Blueprint $table) {
            $table->dropColumn('word_furigana');
        });

        Schema::table('grammar_points', function (Blueprint $table) {
            $table->dropColumn('name_furigana');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['question_furigana', 'explanation_furigana']);
        });
    }
}; 