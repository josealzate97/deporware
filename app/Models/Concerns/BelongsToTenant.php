<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
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

            // Sin contexto de tenant (consola, seeders, ROOT global) → sin filtro
            if ($tenant === null) {
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
