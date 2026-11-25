<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Logro extends Model
{
    protected $table = 'logros';

    public $timestamps = false;

    protected $fillable = ['nombre', 'descripcion', 'condicion', 'valor'];

    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'logro_user', 'logro_id', 'alumno_id')
            ->withPivot('obtenido_at');
    }
}
