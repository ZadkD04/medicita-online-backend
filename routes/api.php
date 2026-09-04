<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->toFrontendArray(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/profile', [ProfileController::class, 'update']);

    Route::apiResource('citas', CitaController::class);

    Route::get('/users', [UserController::class, 'index'])
        ->middleware('permission:usuarios.listar');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
        ->middleware('permission:usuarios.cambiar_rol');
});
