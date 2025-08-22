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
            // Add part_of_speech as json column to allow multiple parts of speech
            // The original migration didn't actually create this column
            $table->json('part_of_speech')->nullable()->after('word_english');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocabulary', function (Blueprint $table) {
            $table->dropColumn('part_of_speech');
        });
    }
};
