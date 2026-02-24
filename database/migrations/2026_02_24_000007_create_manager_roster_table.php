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
        Schema::create('manager_roster', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user');
            $table->uuid('team');
            $table->uuid('role');
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('user')->references('id')->on('users');
            $table->foreign('team')->references('id')->on('teams');
            $table->foreign('role')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manager_roster');
    }
};
