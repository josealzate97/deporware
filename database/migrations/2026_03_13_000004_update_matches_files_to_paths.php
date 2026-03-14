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
        if (!Schema::hasColumn('matches', 'match_file') || !Schema::hasColumn('matches', 'team_picture')) {
            return;
        }

        DB::table('matches')->update([
            'match_file' => null,
            'team_picture' => null,
        ]);

        DB::statement('ALTER TABLE matches MODIFY match_file VARCHAR(255) NULL');
        DB::statement('ALTER TABLE matches MODIFY team_picture VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('matches', 'match_file') || !Schema::hasColumn('matches', 'team_picture')) {
            return;
        }

        DB::statement('ALTER TABLE matches MODIFY match_file BLOB NULL');
        DB::statement('ALTER TABLE matches MODIFY team_picture BLOB NULL');
    }
};
