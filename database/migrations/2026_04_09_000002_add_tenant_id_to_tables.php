<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega tenant_id (nullable por ahora para no romper datos existentes).
 * Después de poblar tenant_id en todos los registros se puede hacer NOT NULL
 * con una migración adicional.
 */
return new class extends Migration
{
    private array $tables = [
        'users',
        'configurations',
        'teams',
        'players',
        'sports_venues',
        'trainings',
        'matches',
        'rival_teams',
        'attack_points',
        'defensive_points',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->uuid('tenant_id')->nullable()->after('id');
                $t->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign(["{$table}_tenant_id_foreign"]);
                $t->dropColumn('tenant_id');
            });
        }
    }
};
