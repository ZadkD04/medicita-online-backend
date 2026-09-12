<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CitaController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('citas.listar');

        $user = $request->user();
        $query = Cita::with(['doctor', 'patient'])->latest();

        if ($user->hasPermission('citas.listar_todas')) {
            return $query->get();
        }

        if ($user->hasRole('doctor')) {
            $query->where('doctor_id', $user->id);
        } else {
            $query->where('patient_id', $user->id);
        }

        return $query->get();
    }

public function store(Request $request)
    {
        Gate::authorize('citas.crear');

        $user = $request->user();

        // Se agregaron specialty y reason a las reglas de validación
        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'specialty' => 'nullable|string|max:150',
            'reason' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        if (!$user->hasRole('doctor') && !$user->hasPermission('citas.listar_todas')) {
            $validatedData['patient_id'] = $user->id;
        } elseif (empty($validatedData['patient_id'])) {
            $validatedData['patient_id'] = $user->id;
        }

        $validatedData['status'] = $this->normalizeStatus($validatedData['status'] ?? 'pendiente');

        $cita = Cita::create($validatedData);

        return response()->json($cita->load(['doctor', 'patient']), 201);
    }

    public function show(Request $request, Cita $cita)
    {
        Gate::authorize('citas.listar');

        if (!$this->canAccess($request->user(), $cita)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $cita->load(['doctor', 'patient']);
    }

    public function update(Request $request, Cita $cita)
    {
        Gate::authorize('citas.editar');

        if (!$this->canAccess($request->user(), $cita)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $data = $request->validate([
            'status' => 'required|string',
            'appointment_date' => 'nullable|date',
            'appointment_time' => 'sometimes|date_format:H:i',
        ]);

        $data['status'] = $this->normalizeStatus($data['status']);

        $cita->update($data);

        return $cita->load(['doctor', 'patient']);
    }

    public function destroy(Request $request, Cita $cita)
    {
        Gate::authorize('citas.eliminar');

        if (!$this->canAccess($request->user(), $cita)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $cita->delete();
        return response()->json(null, 204);
    }

    private function canAccess($user, Cita $cita): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasPermission('citas.listar_todas')) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return (int) $cita->doctor_id === (int) $user->id;
        }

        return (int) $cita->patient_id === (int) $user->id;
    }

    private function normalizeStatus(?string $status): string
    {
        $value = strtolower(trim((string) $status));

        return match ($value) {
            'cancelada', 'cancelled', 'cancelado', 'canceled' => 'cancelada',
            default => 'pendiente',
        };
    }
}
