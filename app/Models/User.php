<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'specialty',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'peso',
        'altura',
        'direccion',
        'ciudad',
        'contacto_emergencia',
        'telefono_emergencia',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function hasRole(string $nombre): bool
    {
        if ($this->relationLoaded('roles')) {
            if ($this->roles->contains('nombre', $nombre)) {
                return true;
            }
        } elseif ($this->roles()->where('nombre', $nombre)->exists()) {
            return true;
        }

        return $this->role === $nombre;
    }

    public function hasPermission(string $key): bool
    {
        return in_array($key, $this->permissionKeys(), true);
    }

    public function permissionKeys(): array
    {
        $this->loadMissing('roles.permissions');

        $keys = $this->roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('clave'))
            ->unique()
            ->values()
            ->all();

        if ($keys !== []) {
            return $keys;
        }

        return match ($this->role) {
            'admin' => Permission::KEYS,
            'doctor' => [
                'citas.listar',
                'citas.editar',
                'perfil.ver',
                'perfil.editar',
                'doctor.panel',
            ],
            default => [
                'citas.listar',
                'citas.crear',
                'citas.eliminar',
                'perfil.ver',
                'perfil.editar',
            ],
        };
    }

    public function syncRole(string $roleName): void
    {
        $role = Role::where('nombre', $roleName)->first();

        if (!$role) {
            $role = Role::where('nombre', 'paciente')->firstOrFail();
            $roleName = 'paciente';
        }

        $this->roles()->sync([$role->id]);
        $this->forceFill(['role' => $roleName])->save();
        $this->unsetRelation('roles');
    }

    public function primaryRoleName(): string
    {
        $this->loadMissing('roles');

        return $this->roles->first()?->nombre ?: ($this->role ?: 'paciente');
    }

    public function toFrontendArray(): array
    {
        $this->loadMissing('roles.permissions');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->primaryRoleName(),
            'roles' => $this->roles->pluck('nombre')->values()->all(),
            'permissions' => $this->permissionKeys(),
            'phone' => $this->telefono,
            'specialty' => $this->specialty,
        ];
    }

    public function doctorCitas()
    {
        return $this->hasMany(Cita::class, 'doctor_id');
    }

    public function patientCitas()
    {
        return $this->hasMany(Cita::class, 'patient_id');
    }
}
