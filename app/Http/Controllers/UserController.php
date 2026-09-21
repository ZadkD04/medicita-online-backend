<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        Gate::authorize('usuarios.listar');

        $users = User::query()
            ->with('roles')
            ->whereDoesntHave('roles', fn ($query) => $query->where('nombre', 'admin'))
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => $user->toFrontendArray());

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function updateRole(Request $request, User $user)
    {
        Gate::authorize('usuarios.cambiar_rol');

        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede modificar el rol de un administrador.',
            ], 403);
        }

        $validatedData = $request->validate([
            'role' => ['required', 'string', Rule::in(['paciente', 'doctor'])],
        ]);

        $user->syncRole($validatedData['role']);

        return response()->json([
            'success' => true,
            'message' => 'Rol de usuario actualizado correctamente.',
            'user' => $user->fresh()->toFrontendArray(),
        ]);
    }
}
