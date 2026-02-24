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
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('match_date');
            $table->uuid('team');
            $table->uuid('rival');
            $table->unsignedTinyInteger('match_status');
            $table->unsignedTinyInteger('match_result');
            $table->unsignedTinyInteger('side');
            $table->string('final_score', 20);
            $table->text('match_notes')->nullable();
            $table->binary('match_file');
            $table->timestamps();

            $table->foreign('team')->references('id')->on('teams');
            $table->foreign('rival')->references('id')->on('rival_teams');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
