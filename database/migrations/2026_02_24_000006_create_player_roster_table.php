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
        Schema::create('player_roster', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player');
            $table->uuid('team');
            $table->integer('position');
            $table->integer('dorsal');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('player')->references('id')->on('players');
            $table->foreign('team')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_roster');
    }
};
