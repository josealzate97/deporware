<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade número correlativo único a cada tenant.
 * El slug pasa a ser auto-generado: slugify(name) + '_' + pad(number, 3)
 * Ej: "Drogueria Luz" + número 2 → "drogueria_luz_002"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->unsignedSmallInteger('number')->unique()->after('id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropUnique(['number']);
            $table->dropColumn('number');
        });
    }
};
