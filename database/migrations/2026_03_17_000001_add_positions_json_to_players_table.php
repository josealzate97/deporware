<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->json('positions')->nullable()->after('position');
        });

        DB::table('players')
            ->select(['id', 'position'])
            ->orderBy('id')
            ->chunkById(100, function ($players) {
                foreach ($players as $player) {
                    DB::table('players')
                        ->where('id', $player->id)
                        ->update([
                            'positions' => $player->position !== null ? json_encode([(int) $player->position]) : json_encode([]),
                        ]);
                }
            }, 'id');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('positions');
        });
    }
};
