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
            $table->json('also_accepted')->nullable()->after('tags')
                ->comment('Alternative accepted answers for both Japanese and English');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vocabulary', function (Blueprint $table) {
            $table->dropColumn('also_accepted');
        });
    }
};