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
        Schema::create('trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 250);
            $table->uuid('team');
            $table->integer('duration');
            $table->text('notes')->nullable();
            $table->integer('tactic_obj')->nullable();
            $table->integer('fisic_obj')->nullable();
            $table->integer('tecnic_obj')->nullable();
            $table->integer('pyscho_obj')->nullable();
            $table->integer('moment')->nullable();
            $table->binary('document')->nullable();
            $table->unsignedTinyInteger('status');
            $table->timestamps();

            $table->foreign('team')->references('id')->on('teams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
