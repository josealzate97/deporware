<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador para la autenticación de usuarios.
 *
 * Flujo multi-tenant:
 *  - Sin slug  → login de ROOT (role = ROLE_ROOT)
 *  - Con slug  → busca tenant activo por slug, luego autentica con tenant_id
 */
class AuthController extends Controller {

    public function showLoginForm() {
        return view('backend.auth.login');
    }

    public function login(Request $request) {

        $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string',
            'slug'     => 'nullable|string|max:60',
        ]);

        $slug     = trim((string) $request->input('slug', ''));
        $username = $request->input('username');
        $password = $request->input('password');

        if ($slug === '') {
            // ── Login ROOT ──────────────────────────────────────────────────
            // Solo usuarios con role ROOT pueden ingresar sin escuela
            $credentials = [
                'username' => $username,
                'password' => $password,
                'role'     => User::ROLE_ROOT,
            ];
        } else {
            // ── Login con escuela ────────────────────────────────────────────
            $tenant = Tenant::where('slug', $slug)
                ->where('status', Tenant::ACTIVE)
                ->first();

            if (! $tenant) {
                return back()
                    ->withErrors(['slug' => 'Escuela no encontrada o inactiva.'])
                    ->withInput($request->only('username', 'slug'));
            }

            $credentials = [
                'username'  => $username,
                'password'  => $password,
                'tenant_id' => $tenant->id,
            ];
        }

        if (Auth::attempt($credentials)) {

            if (Auth::user()->status === User::ACTIVE) {
                $request->session()->regenerate();
                return redirect()->intended(route('home'));
            }

            Auth::logout();
            return back()->withErrors([
                'username' => 'Tu usuario está inactivo. Contacta al administrador.',
            ])->withInput($request->only('username', 'slug'));
        }

        return back()->withErrors([
            'username' => 'Usuario o contraseña incorrectos.',
        ])->withInput($request->only('username', 'slug'));
    }

    public function logout() {
        Auth::logout();
        return redirect()->route('login');
    }
}
