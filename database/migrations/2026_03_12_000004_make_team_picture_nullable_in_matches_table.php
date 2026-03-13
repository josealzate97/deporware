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
        if (Schema::hasColumn('matches', 'team_picture')) {
            DB::statement('ALTER TABLE matches MODIFY team_picture BLOB NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('matches', 'team_picture')) {
            DB::statement('ALTER TABLE matches MODIFY team_picture BLOB NOT NULL');
        }
    }
};
