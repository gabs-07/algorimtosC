<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Leccion extends Model
{
    protected $table = 'lecciones';

    public $timestamps = false;

    protected $fillable = ['unidad_id', 'titulo', 'contenido', 'orden'];

    public function unidad(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id');
    }

    public function ejercicios(): HasMany
    {
        return $this->hasMany(Ejercicio::class, 'leccion_id')->orderBy('orden');
    }
}
