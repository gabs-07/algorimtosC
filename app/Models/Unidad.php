<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidad extends Model
{
    protected $table = 'unidades';

    public $timestamps = false;

    protected $fillable = ['titulo', 'descripcion', 'orden', 'publicada'];

    protected function casts(): array
    {
        return ['publicada' => 'boolean'];
    }

    public function lecciones(): HasMany
    {
        return $this->hasMany(Leccion::class, 'unidad_id')->orderBy('orden');
    }
}
