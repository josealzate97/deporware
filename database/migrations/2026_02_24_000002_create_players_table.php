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
        Schema::create('players', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('lastname', 100);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->date('birthdate');
            $table->integer('nacionality');
            $table->integer('position');
            $table->integer('dorsal')->nullable();
            $table->integer('foot');
            $table->integer('weight');
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
