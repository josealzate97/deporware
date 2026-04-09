<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait BelongsToTenant
 *
 * Aplica un Global Scope que filtra automáticamente por el tenant activo.
 * Al crear un registro, setea tenant_id desde el contexto de la app.
 *
 * Uso: añadir `use BelongsToTenant;` en cualquier modelo que tenga tenant_id.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // Global Scope: filtrar por tenant en todas las queries
        static::addGlobalScope('tenant', function (Builder $query) {
            $tenant = static::resolveTenant();

            if ($tenant === null) {
                return; // Sin tenant en contexto (ej: consola, seeders)
            }

            // ROOT puede ver todo sin restricción
            $user = auth()->user();
            
            if ($user && (int) $user->role === User::ROLE_ROOT) {
                return;
            }

            $query->where(static::getModel()->getTable() . '.tenant_id', $tenant->id);
        });

        // Al crear: inyectar tenant_id automáticamente
        static::creating(function (Model $model) {
            
            if (empty($model->tenant_id)) {

                $tenant = static::resolveTenant();
                
                if ($tenant) {
                    $model->tenant_id = $tenant->id;
                }

            }

        });
    }

    private static function resolveTenant(): ?Tenant
    {
        if (!app()->bound('current_tenant')) {
            return null;
        }

        return app('current_tenant');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
