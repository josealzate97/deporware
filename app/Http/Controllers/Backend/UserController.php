<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $roles = [
            User::ROLE_ROOT => 'Super Admin',
            User::ROLE_ADMIN => 'Gerente',
            User::ROLE_STAFF => 'Entrenador',
            User::ROLE_PLAYER => 'Jugador',
        ];

        return view('backend.users.new', compact('roles'));

    }

    /**
     * Muestra la lista de usuarios.
     * 
     * @return \Illuminate\View\View
     * Retorna la vista `backend.users.index` con la lista de usuarios.
    */
    public function index() {

        $users = User::orderBy('name', 'asc')->paginate(10);
        
        return view('backend.users.index', compact('users'));

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
        $user = User::findOrFail($id);

        // Roles
        $roles = [
            User::ROLE_ROOT => 'Super Admin',
            User::ROLE_ADMIN => 'Gerente',
            User::ROLE_STAFF => 'Entrenador',
            User::ROLE_PLAYER => 'Jugador',
        ];
        
        return view('backend.users.info', compact('user', 'roles'));

    }

    /**
     * Crea un nuevo usuario.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
    */
    public function store(Request $request) {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'phone' => 'required|string|max:255|unique:users,phone',
            'role' => 'required|integer',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        User::create([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => User::ACTIVE,
        ]);

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

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'role' => 'required|integer',
            'status' => 'required|integer',
            'email' => 'required|email|max:255',
            'new_password' => 'nullable|string|min:8',
        ]);

        // Actualiza los datos del usuario
        $user->update([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'],
            'username' => $validated['username'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ]);

        // Si se proporciona una nueva contraseña, actualízala
        if (!empty($validated['new_password'])) {

            $user->password = Hash::make($validated['new_password']);
            $user->save();

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

        // Buscamos el usuario por ID        
        $user = User::findOrFail($id);

        // Cambia el estado del usuario a inactivo
        $user->update(['status' => User::INACTIVE]);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Usuario eliminado correctamente'
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