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
        Schema::create('team_venue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('team');
            $table->uuid('venue');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('team')->references('id')->on('teams');
            $table->foreign('venue')->references('id')->on('sports_venues');

            $table->unique(['team', 'venue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_venue');
    }
};
