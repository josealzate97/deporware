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
        Schema::create('user_venue', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user');
            $table->uuid('venue');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('user')->references('id')->on('users');
            $table->foreign('venue')->references('id')->on('sports_venues');

            $table->unique(['user', 'venue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_venue');
    }
};
