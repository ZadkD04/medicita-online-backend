<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => 'paciente',
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = $validatedData['email'];
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
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }
}
