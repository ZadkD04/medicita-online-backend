<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Cita::with(['doctor', 'patient'])->latest();

        if ($user->role === 'doctor') {
            $query->where('doctor_id', $user->id);
        } else {
            $query->where('patient_id', $user->id);
        }

        return $query->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'nullable|string',
        ]);

        if ($user->role !== 'doctor') {
            $validatedData['patient_id'] = $user->id;
        } elseif (empty($validatedData['patient_id'])) {
            $validatedData['patient_id'] = $user->id;
        }

        $validatedData['status'] = $this->normalizeStatus($validatedData['status'] ?? 'pendiente');

        $cita = Cita::create($validatedData);

        return response()->json($cita->load(['doctor', 'patient']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Cita $cita)
    {
        if (!$this->canAccess($request->user(), $cita)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $cita->load(['doctor', 'patient']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cita $cita)
    {
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Cita $cita)
    {
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

        if ($user->role === 'doctor') {
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
