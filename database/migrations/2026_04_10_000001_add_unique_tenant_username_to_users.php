<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asegura que username sea único dentro de cada tenant.
 * ROOT (tenant_id = null) tiene su propio espacio de nombres.
 * MySQL no considera dos NULLs como iguales en índices únicos,
 * por lo que múltiples usuarios ROOT con distinto username son válidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique(['tenant_id', 'username'], 'users_tenant_username_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_tenant_username_unique');
        });
    }
};
