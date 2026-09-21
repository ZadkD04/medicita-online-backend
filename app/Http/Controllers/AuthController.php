<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->merge([
            'password_confirmation' => $request->input(
                'password_confirmation',
                $request->input('confirmPassword')
            ),
        ]);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
            'role' => 'paciente',
        ]);

        $user->syncRole('paciente');

        return response()->json([
            'success' => true,
            'user' => $user->fresh()->toFrontendArray(),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = trim($validatedData['email']);
        $user = User::where(function ($query) use ($identifier) {
            $query->where('email', $identifier)
                ->orWhere('name', $identifier);
        })->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales incorrectas',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user->toFrontendArray(),
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}
