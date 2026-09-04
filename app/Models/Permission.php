<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    public const KEYS = [
        'usuarios.listar',
        'usuarios.cambiar_rol',
        'admin.acceder',
        'citas.listar',
        'citas.listar_todas',
        'citas.crear',
        'citas.editar',
        'citas.eliminar',
        'perfil.ver',
        'perfil.editar',
        'doctor.panel',
    ];

    protected $fillable = [
        'clave',
        'descripcion',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }
}
