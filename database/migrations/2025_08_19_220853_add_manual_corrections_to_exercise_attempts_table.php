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
        Schema::table('exercise_attempts', function (Blueprint $table) {
            $table->integer('original_score')->default(0)->after('score'); // Auto-graded score
            $table->json('manual_corrections')->nullable()->after('question_results'); // Manually accepted question IDs
            $table->timestamp('last_corrected_at')->nullable()->after('manual_corrections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_attempts', function (Blueprint $table) {
            $table->dropColumn(['original_score', 'manual_corrections', 'last_corrected_at']);
        });
    }
};
