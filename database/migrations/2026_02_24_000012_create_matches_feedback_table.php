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
        Schema::create('matches_feedback', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('match');
            $table->string('match_formation', 20);
            $table->integer('attack_strengths')->nullable();
            $table->integer('attack_weaknesses')->nullable();
            $table->integer('defense_strengths')->nullable();
            $table->integer('defense_weaknesses')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('match')->references('id')->on('matches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches_feedback');
    }
};
