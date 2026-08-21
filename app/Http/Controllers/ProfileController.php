<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
            ], 401);
        }

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
            'user' => $user->fresh(),
        ]);
    }
}
