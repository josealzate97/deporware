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
        Schema::create('training_attendance', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('training');
            $table->uuid('player');
            $table->timestamp('created_at')->nullable();

            $table->foreign('training')->references('id')->on('trainings');
            $table->foreign('player')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_attendance');
    }
};
