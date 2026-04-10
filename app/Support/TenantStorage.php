<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

/**
 * Helper centralizado para rutas de storage multi-tenant.
 *
 * Todos los paths de archivos deben construirse a través de este helper
 * para garantizar el aislamiento entre tenants.
 *
 * Uso:
 *   TenantStorage::path("teams/{$teamId}/players/{$playerId}/photos")
 *   → "{tenantId}/teams/{teamId}/players/{playerId}/photos"
 */
class TenantStorage
{
    /**
     * Devuelve el path completo con el prefijo del tenant activo.
     * Si no hay tenant en el contexto (consola, pruebas), devuelve el path sin prefijo.
     */
    public static function path(string $relative): string
    {
        $tenant = static::tenant();
        return $tenant ? "{$tenant->id}/{$relative}" : $relative;
    }

    /**
     * Crea la estructura de carpetas base para un tenant en storage/public.
     * Se llama automáticamente al crear un nuevo Tenant.
     */
    public static function scaffold(Tenant $tenant): void
    {
        $disk = Storage::disk('public');
        $base = $tenant->id;

        $folders = [
            "{$base}/configurations/logo",
            "{$base}/teams",
        ];

        foreach ($folders as $folder) {
            if (!$disk->exists($folder)) {
                $disk->makeDirectory($folder);
            }
        }
    }

    private static function tenant(): ?Tenant
    {
        return app()->bound('current_tenant') ? app('current_tenant') : null;
    }
}
