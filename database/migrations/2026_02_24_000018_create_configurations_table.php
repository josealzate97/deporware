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
        Schema::create('configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('legal_name', 150)->nullable();
            $table->string('legal_id', 30)->nullable();
            $table->string('country', 5);
            $table->string('city', 100)->nullable();
            $table->string('address', 250)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 250)->nullable();
            $table->string('logo', 250)->nullable();
            $table->string('currency', 5);
            $table->string('timezone', 50);
            $table->string('locale', 10);
            $table->unsignedTinyInteger('sport');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
