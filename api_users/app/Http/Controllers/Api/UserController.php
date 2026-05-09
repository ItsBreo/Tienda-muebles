<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        if (!$request->user()->tokenCan('usuarios.ver')) {
            return $this->errorResponse('No autorizado.', 403);
        }

        $users = User::with('role')->orderByDesc('last_login_at')->get();

        return $this->successResponse($users);
    }

    public function store(Request $request)
    {
        if (!$request->user()->tokenCan('usuarios.crear')) {
            return $this->errorResponse('No autorizado.', 403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role_id'],
            'failed_attempts' => 0,
        ]);

        return $this->successResponse(['user' => $user->load('role')], 'Usuario creado correctamente.', 201);
    }

    public function show(Request $request, User $user)
    {
        $authUser = $request->user();

        if (!$authUser->tokenCan('usuarios.ver') && $authUser->id !== $user->id) {
            return $this->errorResponse('No autorizado.', 403);
        }

        return $this->successResponse($user->load('role'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = $request->user();
        $isSelf = $authUser->id === $user->id;
        $isAdmin = $authUser->tokenCan('usuarios.editar');

        if (!$isAdmin && !($isSelf && $authUser->tokenCan('perfil.ver'))) {
            return $this->errorResponse('No autorizado.', 403);
        }

        $rules = [
            'name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:4',
        ];

        if ($isAdmin) {
            $rules['role_id'] = 'required|exists:roles,id';
        }

        $data = $request->validate($rules);

        $toUpdate = [
            'name' => $data['name'],
            'surname' => $data['surname'] ?? null,
            'email' => $data['email'],
        ];

        if ($isAdmin && isset($data['role_id'])) {
            $toUpdate['role_id'] = $data['role_id'];
        }

        if (!empty($data['password'])) {
            $toUpdate['password'] = Hash::make($data['password']);
        }

        $user->update($toUpdate);

        return $this->successResponse(['user' => $user->fresh()->load('role')], 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->tokenCan('usuarios.eliminar')) {
            return $this->errorResponse('No autorizado.', 403);
        }

        $user->delete();

        return $this->successResponse(null, 'Usuario eliminado correctamente.');
    }

    public function roles(Request $request)
    {
        if (!$request->user()->tokenCan('admin.panel')) {
            return $this->errorResponse('No autorizado.', 403);
        }

        return $this->successResponse(Role::all());
    }
}
