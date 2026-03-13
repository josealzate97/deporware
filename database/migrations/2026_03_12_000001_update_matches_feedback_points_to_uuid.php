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
        Schema::table('matches_feedback', function (Blueprint $table) {
            $table->dropColumn([
                'attack_strengths',
                'attack_weaknesses',
                'defense_strengths',
                'defense_weaknesses',
            ]);
        });

        Schema::table('matches_feedback', function (Blueprint $table) {
            $table->uuid('attack_strengths')->nullable();
            $table->uuid('attack_weaknesses')->nullable();
            $table->uuid('defense_strengths')->nullable();
            $table->uuid('defense_weaknesses')->nullable();

            $table->foreign('attack_strengths')->references('id')->on('attack_points');
            $table->foreign('attack_weaknesses')->references('id')->on('attack_points');
            $table->foreign('defense_strengths')->references('id')->on('defensive_points');
            $table->foreign('defense_weaknesses')->references('id')->on('defensive_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches_feedback', function (Blueprint $table) {
            $table->dropForeign(['attack_strengths']);
            $table->dropForeign(['attack_weaknesses']);
            $table->dropForeign(['defense_strengths']);
            $table->dropForeign(['defense_weaknesses']);

            $table->dropColumn([
                'attack_strengths',
                'attack_weaknesses',
                'defense_strengths',
                'defense_weaknesses',
            ]);
        });

        Schema::table('matches_feedback', function (Blueprint $table) {
            $table->integer('attack_strengths')->nullable();
            $table->integer('attack_weaknesses')->nullable();
            $table->integer('defense_strengths')->nullable();
            $table->integer('defense_weaknesses')->nullable();
        });
    }
};
