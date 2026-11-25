<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = ['docente_id', 'nombre'];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'grupo_user', 'grupo_id', 'alumno_id')
            ->withPivot('created_at');
    }
}
