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
        Schema::table('trainings', function (Blueprint $table) {
            $table->uuid('venue')->nullable()->after('team');
            $table->string('location', 250)->nullable()->after('venue');

            $table->foreign('venue')->references('id')->on('sports_venues');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropForeign(['venue']);
            $table->dropColumn(['venue', 'location']);
        });
    }
};
