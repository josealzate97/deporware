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
        Schema::create('player_observations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('player');
            $table->integer('type');
            $table->text('notes')->nullable();
            $table->uuid('user');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('player')->references('id')->on('players');
            $table->foreign('user')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_observations');
    }
};
