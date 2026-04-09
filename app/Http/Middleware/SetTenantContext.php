<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // ROOT: tenant activo viene de la sesión (elegido desde el selector)
        if ((int) $user->role === \App\Models\User::ROLE_ROOT) {
            $rootTenantId = $request->session()->get('root_tenant_id');
            if ($rootTenantId) {
                $tenant = Tenant::find($rootTenantId);
                if ($tenant) {
                    app()->instance('current_tenant', $tenant);
                }
            }
            // Sin root_tenant_id en sesión → ROOT global, sin tenant activo
            return $next($request);
        }

        // Resto de roles: tenant viene de su propio tenant_id
        if ($user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant) {
                app()->instance('current_tenant', $tenant);
            }
        }

        return $next($request);
    }
}
