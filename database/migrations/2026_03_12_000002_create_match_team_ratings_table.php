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
        Schema::create('match_team_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('match');
            $table->integer('referee_rating')->nullable();
            $table->integer('coach_rating')->nullable();
            $table->integer('teammates_rating')->nullable();
            $table->integer('opponents_rating')->nullable();
            $table->integer('fans_rating')->nullable();
            $table->timestamps();

            $table->foreign('match')->references('id')->on('matches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_team_ratings');
    }
};
