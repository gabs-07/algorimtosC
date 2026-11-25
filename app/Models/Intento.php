<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Intento extends Model
{
    protected $table = 'intentos';

    // Solo existe created_at (ver migración)
    const UPDATED_AT = null;

    protected $fillable = ['alumno_id', 'ejercicio_id', 'respuesta', 'es_correcto'];

    protected function casts(): array
    {
        return ['es_correcto' => 'boolean'];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'ejercicio_id');
    }
}
