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
        Schema::table('matches', function (Blueprint $table) {
            $table->string('match_round', 50)->nullable()->after('match_date');
            $table->unsignedTinyInteger('match_result')->nullable()->change();
            $table->string('final_score', 20)->nullable()->change();
            $table->binary('match_file')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn('match_round');
            $table->unsignedTinyInteger('match_result')->nullable(false)->change();
            $table->string('final_score', 20)->nullable(false)->change();
            $table->binary('match_file')->nullable(false)->change();
        });
    }
};
