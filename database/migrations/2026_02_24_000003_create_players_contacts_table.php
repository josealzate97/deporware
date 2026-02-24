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
        Schema::create('players_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('lastname', 100);
            $table->string('email', 100);
            $table->string('phone', 20);
            $table->string('address', 80)->nullable();
            $table->string('city', 80)->nullable();
            $table->uuid('player');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('player')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players_contacts');
    }
};
