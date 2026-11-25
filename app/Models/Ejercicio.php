<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ejercicio extends Model
{
    protected $table = 'ejercicios';

    public $timestamps = false;

    public const TIPOS = ['opcion_multiple', 'completar', 'ordenar'];

    public const ETIQUETAS = [
        'opcion_multiple' => 'Opción múltiple',
        'completar' => 'Completar código',
        'ordenar' => 'Ordenar líneas',
    ];

    protected $fillable = ['leccion_id', 'tipo', 'enunciado', 'codigo_base', 'orden', 'publicado'];

    protected function casts(): array
    {
        return ['publicado' => 'boolean'];
    }

    public function etiqueta(): string
    {
        return self::ETIQUETAS[$this->tipo] ?? $this->tipo;
    }

    public function leccion(): BelongsTo
    {
        return $this->belongsTo(Leccion::class, 'leccion_id');
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(Opcion::class, 'ejercicio_id');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(Intento::class, 'ejercicio_id');
    }
}
