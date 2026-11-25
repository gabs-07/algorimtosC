<?php

namespace Database\Seeders;

use App\Models\Logro;
use Illuminate\Database\Seeder;

/** Se puede ejecutar varias veces sin duplicar: php artisan db:seed --class=LogroSeeder */
class LogroSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            ['Primer paso', 'Resuelve tu primer ejercicio.', 'ejercicios_resueltos', 1],
            ['Constante', 'Resuelve 5 ejercicios.', 'ejercicios_resueltos', 5],
            ['Dedicado', 'Resuelve 10 ejercicios.', 'ejercicios_resueltos', 10],
            ['Unidad superada', 'Completa una unidad.', 'unidades_completadas', 1],
            ['Doble reto', 'Completa 2 unidades.', 'unidades_completadas', 2],
        ];

        foreach ($catalogo as [$nombre, $descripcion, $condicion, $valor]) {
            Logro::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripcion, 'condicion' => $condicion, 'valor' => $valor]
            );
        }
    }
}
