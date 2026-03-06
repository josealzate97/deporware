<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('teams')) {
            return;
        }

        DB::statement('ALTER TABLE teams MODIFY year VARCHAR(9)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('teams')) {
            return;
        }

        DB::statement('ALTER TABLE teams MODIFY year VARCHAR(4)');
    }
};
