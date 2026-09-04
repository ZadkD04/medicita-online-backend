<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        Gate::authorize('perfil.ver');

        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $user->toFrontendArray(),
        ]);
    }

    public function update(Request $request)
    {
        Gate::authorize('perfil.editar');

        $user = $request->user();

        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'peso' => 'nullable|numeric',
            'altura' => 'nullable|numeric',
            'direccion' => 'nullable|string|max:255',
            'ciudad' => 'nullable|string|max:100',
            'contacto_emergencia' => 'nullable|string|max:255',
            'telefono_emergencia' => 'nullable|string|max:50',
        ]);

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => $user->fresh()->toFrontendArray(),
        ]);
    }
}
