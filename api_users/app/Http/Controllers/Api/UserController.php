<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Devuelve todos los usuarios con su rol.
     */
    public function index(Request $request)
    {
        $users = User::with('role')->orderByDesc('last_login_at')->get();

        return response()->json($users, 200);
    }

    /**
     * POST /api/users
     * Crea un nuevo usuario (uso administrativo).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'surname'  => 'nullable|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'            => $data['name'],
            'surname'         => $data['surname'] ?? null,
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'role_id'         => $data['role_id'],
            'failed_attempts' => 0,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'user'    => $user->load('role'),
        ], 201);
    }

    /**
     * GET /api/users/{id}
     * Muestra un usuario específico.
     */
    public function show(User $user)
    {
        return response()->json($user->load('role'), 200);
    }

    /**
     * PUT /api/users/{id}
     * Actualiza un usuario existente.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'surname'  => 'nullable|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role_id'  => 'required|exists:roles,id',
            'password' => 'nullable|min:4',
        ]);

        $toUpdate = [
            'name'    => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ];

        if (!empty($data['password'])) {
            $toUpdate['password'] = Hash::make($data['password']);
        }

        $user->update($toUpdate);

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'user'    => $user->fresh()->load('role'),
        ], 200);
    }

    /**
     * DELETE /api/users/{id}
     * Elimina un usuario.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente.'], 200);
    }

    /**
     * GET /api/roles
     * Devuelve todos los roles disponibles.
     */
    public function roles()
    {
        return response()->json(Role::all(), 200);
    }
}
