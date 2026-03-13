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
        if (!Schema::hasColumn('players_contacts', 'relationship')) {
            return;
        }

        DB::table('players_contacts')
            ->where('relationship', 'mother')
            ->update(['relationship' => 1]);

        DB::table('players_contacts')
            ->where('relationship', 'father')
            ->update(['relationship' => 2]);

        DB::table('players_contacts')
            ->where('relationship', 'sibling')
            ->update(['relationship' => 3]);

        DB::table('players_contacts')
            ->where('relationship', 'uncle_aunt')
            ->update(['relationship' => 4]);

        DB::table('players_contacts')
            ->where('relationship', 'cousin')
            ->update(['relationship' => 5]);

        DB::statement('ALTER TABLE players_contacts MODIFY relationship TINYINT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('players_contacts', 'relationship')) {
            return;
        }

        DB::statement('ALTER TABLE players_contacts MODIFY relationship VARCHAR(30) NULL');

        DB::table('players_contacts')
            ->where('relationship', 1)
            ->update(['relationship' => 'mother']);

        DB::table('players_contacts')
            ->where('relationship', 2)
            ->update(['relationship' => 'father']);

        DB::table('players_contacts')
            ->where('relationship', 3)
            ->update(['relationship' => 'sibling']);

        DB::table('players_contacts')
            ->where('relationship', 4)
            ->update(['relationship' => 'uncle_aunt']);

        DB::table('players_contacts')
            ->where('relationship', 5)
            ->update(['relationship' => 'cousin']);
    }
};
