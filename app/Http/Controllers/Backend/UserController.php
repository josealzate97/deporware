<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\ManagerRoster;
use App\Models\SportsVenue;
use App\Models\User;

/**
 * Controlador para la gestión de usuarios.
 * 
 * @author Jose Alzate
 * @date 13 de julio de 2025
*/
class UserController extends Controller {

    /**
     * Muestra el formulario para crear un usuario.
     *
     * @return \Illuminate\View\View
    */
    public function create() {

        $roles = User::roleOptions();
        $venues = SportsVenue::where('status', true)->orderBy('name')->get();

        return view('backend.users.new', compact('roles', 'venues'));

    }

    /**
     * Muestra la lista de usuarios.
     * 
     * @return \Illuminate\View\View
     * Retorna la vista `backend.users.index` con la lista de usuarios.
    */
    public function index(Request $request) {

        $search = trim((string) $request->query('search', ''));
        $role = (string) $request->query('role', '');
        $roles = User::roleOptions();

        if ($role !== '' && !array_key_exists((int) $role, $roles)) {
            $role = '';
        }

        $usersQuery = User::query()
            ->orderByDesc('status')
            ->orderBy('name', 'asc');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($role !== '') {
            $usersQuery->where('role', (int) $role);
        }

        $users = $usersQuery
            ->paginate(10)
            ->withQueryString();
        
        return view('backend.users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'selectedRole' => $role,
        ]);

    }

    /**
     * Muestra la información de un usuario específico.
     * 
     * @param int $id
     * ID del usuario que se desea consultar.
     * 
     * @return \Illuminate\View\View
     * Retorna la vista `backend.users.info` con los datos del usuario y los roles disponibles.
    */
    public function info($id) {   

        // Informacion del usuario
        $user = User::with('venues')->findOrFail($id);

        if (request()->boolean('modal')) {
            $venues = $user->venues
                ->where('pivot.status', 1)
                ->values();

            $teamAssignments = collect();
            if (in_array($user->role, [User::ROLE_COACH, User::ROLE_COORDINATOR], true)) {
                $teamAssignments = ManagerRoster::with('teamModel')
                    ->where('user', $user->id)
                    ->get()
                    ->filter(fn ($assignment) => $assignment->teamModel)
                    ->sortBy(fn ($assignment) => $assignment->teamModel->name ?? '')
                    ->values();
            }

            return view('backend.users.info-modal', compact('user', 'venues', 'teamAssignments'));
        }

        // Roles
        $roles = User::roleOptions();
        $venues = SportsVenue::orderBy('name')->get();
        
        return view('backend.users.info', compact('user', 'roles', 'venues'));

    }

    /**
     * Crea un nuevo usuario.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function store(Request $request) {

        $role = (int) $request->input('role');
        $requiresVenues = !in_array($role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER], true);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'phone' => 'required|string|max:255|unique:users,phone',
            'role' => 'required|integer',
            'hired_date' => 'required|date',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'venues' => [$requiresVenues ? 'required' : 'nullable', 'array'],
            'venues.*' => 'uuid|exists:sports_venues,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'hired_date' => $validated['hired_date'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => User::ACTIVE,
        ]);

        if ($requiresVenues) {
            $venueIds = $validated['venues'] ?? [];
            $user->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        }

        return redirect()->route('users.index');

    }

    /**
     * Actualiza la información de un usuario específico.
     * 
     * @param \Illuminate\Http\Request $request
     * Objeto de la solicitud HTTP que contiene los datos enviados por el cliente.
     * 
     * @param int $id
     * ID del usuario que se desea actualizar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Retorna una respuesta JSON indicando el éxito o error de la operación.
    */
    public function update(Request $request, $id) {

        if (!Auth::check() || !in_array(Auth::user()->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COACH], true)) {
            
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para editar usuarios.'
            ], 403);

        }

        $user = User::findOrFail($id);

        $role = (int) $request->input('role');
        $requiresVenues = !in_array($role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER], true);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'role' => 'required|integer',
            'hired_date' => 'required|date',
            'status' => 'required|integer',
            'email' => 'required|email|max:255',
            'new_password' => 'nullable|string|min:8',
            'venues' => [$requiresVenues ? 'required' : 'nullable', 'array'],
            'venues.*' => 'uuid|exists:sports_venues,id',
        ]);

        // Actualiza los datos del usuario
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'hired_date' => $validated['hired_date'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ]);

        // Si se proporciona una nueva contraseña, actualízala
        if (!empty($validated['new_password'])) {

            $user->password = Hash::make($validated['new_password']);
            $user->save();

        }

        if ($requiresVenues) {
            $venueIds = $validated['venues'] ?? [];
            $user->venues()->syncWithPivotValues($venueIds, ['status' => 1]);
        } else {
            $user->venues()->detach();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Usuario actualizado correctamente'
        ]);
    
    }

    /**
     * Elimina un usuario estableciendo su estado a inactivo.
     * 
     * @param int $id
     * ID del usuario que se desea eliminar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Retorna una respuesta JSON indicando el éxito o error de la operación.
     * 
    */
    public function delete($id) {

        if (!Auth::check() || !in_array(Auth::user()->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COACH], true)) {
            
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para desactivar usuarios.'
            ], 403);

        }

        // Buscamos el usuario por ID        
        $user = User::findOrFail($id);

        // Cambia el estado del usuario a inactivo
        $user->update(['status' => User::INACTIVE]);
        $user->save();

        $isSelf = Auth::check() && (string) Auth::id() === (string) $user->id;

        if ($isSelf) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return response()->json([
            'success' => true, 
            'message' => 'Usuario eliminado correctamente',
            'logout' => $isSelf,
            'redirect' => $isSelf ? route('login') : null,
        ]);

    }


    /**
     * Activa un usuario estableciendo su estado a activo.
     * 
     * @param int $id
     * ID del usuario que se desea activar.
     * 
     * @return \Illuminate\Http\JsonResponse
     * Retorna una respuesta JSON indicando el éxito o error de la operación.
    */
    public function activate($id) {

        if (
            !Auth::check() || !in_array(Auth::user()->role, 
            [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER, User::ROLE_COACH], true)
        ) {
            
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para activar usuarios.'
            ], 403);

        }

        $user = User::findOrFail($id);

        // Cambia el estado del usuario a activo
        $user->update(['status' => User::ACTIVE]);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Usuario activado correctamente'
        ]);
    }

}
