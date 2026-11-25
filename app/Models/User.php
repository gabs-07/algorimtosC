<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROL_ADMIN = 'admin';
    public const ROL_DOCENTE = 'docente';
    public const ROL_ALUMNO = 'alumno';

    // 'name' se deja fillable solo por compatibilidad con Breeze
    // (ver mutador setNameAttribute más abajo). No se guarda en la BD.
    protected $fillable = ['nombre', 'name', 'email', 'password', 'rol', 'activo'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /* Compatibilidad con Breeze: sus vistas y controladores usan "name",
       pero la columna real es "nombre". */
    public function getNameAttribute(): ?string
    {
        return $this->attributes['nombre'] ?? null;
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['nombre'] = $value;
    }

    /* Roles */
    public function esAdmin(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esDocente(): bool
    {
        return $this->rol === self::ROL_DOCENTE;
    }

    public function esAlumno(): bool
    {
        return $this->rol === self::ROL_ALUMNO;
    }

    /* Relaciones */

    // Grupos que creó (si es docente)
    public function gruposCreados(): HasMany
    {
        return $this->hasMany(Grupo::class, 'docente_id');
    }

    // Grupos en los que está inscrito (si es alumno)
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(Grupo::class, 'grupo_user', 'alumno_id', 'grupo_id')
            ->withPivot('created_at');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(Intento::class, 'alumno_id');
    }

    public function desbloqueos(): HasMany
    {
        return $this->hasMany(Desbloqueo::class, 'alumno_id');
    }

    public function logros(): BelongsToMany
    {
        return $this->belongsToMany(Logro::class, 'logro_user', 'alumno_id', 'logro_id')
            ->withPivot('obtenido_at');
    }
}
