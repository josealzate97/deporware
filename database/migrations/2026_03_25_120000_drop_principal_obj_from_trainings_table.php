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
        if (!Schema::hasColumn('trainings', 'principal_obj')) {
            return;
        }

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn('principal_obj');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('trainings', 'principal_obj')) {
            return;
        }

        Schema::table('trainings', function (Blueprint $table) {
            $table->integer('principal_obj')->nullable()->after('notes');
        });
    }
};

