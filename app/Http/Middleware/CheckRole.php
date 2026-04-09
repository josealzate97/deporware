<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Mapa de alias de rol → constante del modelo User.
     */
    private const ROLE_MAP = [
        'root'          => User::ROLE_ROOT,
        'sport_manager' => User::ROLE_SPORT_MANAGER,
        'coordinator'   => User::ROLE_COORDINATOR,
        'coach'         => User::ROLE_COACH,
    ];

    /**
     * Handle an incoming request.
     *
     * Uso en rutas:  ->middleware('role:root,sport_manager')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        foreach ($roles as $alias) {
            $roleValue = self::ROLE_MAP[$alias] ?? null;

            if ($roleValue !== null && (int) $user->role === $roleValue) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a este módulo.');
    }
}
