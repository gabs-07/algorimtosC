<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opcion extends Model
{
    protected $table = 'opciones';

    public $timestamps = false;

    protected $fillable = ['ejercicio_id', 'texto', 'es_correcta', 'posicion_correcta'];

    protected function casts(): array
    {
        return ['es_correcta' => 'boolean'];
    }

    public function ejercicio(): BelongsTo
    {
        return $this->belongsTo(Ejercicio::class, 'ejercicio_id');
    }
}
