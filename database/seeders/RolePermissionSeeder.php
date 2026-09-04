<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Administrador del sistema',
            'doctor' => 'Médico que gestiona citas asignadas',
            'paciente' => 'Paciente que solicita y consulta citas',
        ];

        foreach ($roles as $nombre => $descripcion) {
            Role::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion]
            );
        }

        $permissions = [
            'usuarios.listar' => 'Listar usuarios del sistema',
            'usuarios.cambiar_rol' => 'Asignar rol de doctor o paciente',
            'admin.acceder' => 'Entrar al panel de administración',
            'citas.listar' => 'Ver citas propias o asignadas',
            'citas.listar_todas' => 'Ver todas las citas',
            'citas.crear' => 'Solicitar una cita',
            'citas.editar' => 'Aceptar, rechazar o actualizar una cita',
            'citas.eliminar' => 'Cancelar o eliminar una cita',
            'perfil.ver' => 'Ver el perfil propio',
            'perfil.editar' => 'Actualizar el perfil propio',
            'doctor.panel' => 'Acceder al panel médico',
        ];

        foreach ($permissions as $clave => $descripcion) {
            Permission::updateOrCreate(
                ['clave' => $clave],
                ['descripcion' => $descripcion]
            );
        }

        $matrix = [
            'admin' => Permission::KEYS,
            'doctor' => [
                'citas.listar',
                'citas.editar',
                'perfil.ver',
                'perfil.editar',
                'doctor.panel',
            ],
            'paciente' => [
                'citas.listar',
                'citas.crear',
                'citas.eliminar',
                'perfil.ver',
                'perfil.editar',
            ],
        ];

        foreach ($matrix as $roleName => $keys) {
            $role = Role::where('nombre', $roleName)->firstOrFail();
            $permissionIds = Permission::whereIn('clave', $keys)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
