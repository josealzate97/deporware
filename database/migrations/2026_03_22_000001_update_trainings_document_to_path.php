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
        if (!Schema::hasColumn('trainings', 'document')) {
            return;
        }

        DB::table('trainings')->update([
            'document' => null,
        ]);

        DB::statement('ALTER TABLE trainings MODIFY document VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('trainings', 'document')) {
            return;
        }

        DB::statement('ALTER TABLE trainings MODIFY document BLOB NULL');
    }
};
