<?php

namespace App\Services;

use App\Models\Logro;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Otorga logros automáticamente al cumplirse su condición (RF020).
 *
 * Condiciones del catálogo (columna logros.condicion):
 *  - ejercicios_resueltos:  cantidad de ejercicios distintos resueltos correctamente
 *  - unidades_completadas:  cantidad de unidades completadas
 */
class LogroService
{
    public function __construct(private RutaAprendizaje $ruta)
    {
    }

    /** @return Collection<int, Logro> logros que se otorgaron en esta llamada */
    public function otorgar(User $alumno): Collection
    {
        $obtenidos = $alumno->logros()->pluck('logros.id');
        $pendientes = Logro::whereNotIn('id', $obtenidos)->get();

        if ($pendientes->isEmpty()) {
            return collect();
        }

        $ejerciciosResueltos = $this->ruta->idsResueltos($alumno)->count();
        $unidadesCompletadas = $this->ruta->estados($alumno)
            ->filter(fn ($i) => $i['total'] > 0 && $i['estado'] === RutaAprendizaje::COMPLETADA)
            ->count();

        $nuevos = collect();

        foreach ($pendientes as $logro) {
            $actual = match ($logro->condicion) {
                'ejercicios_resueltos' => $ejerciciosResueltos,
                'unidades_completadas' => $unidadesCompletadas,
                default => null,
            };

            if ($actual !== null && $logro->valor !== null && $actual >= $logro->valor) {
                $alumno->logros()->attach($logro->id, ['obtenido_at' => now()]);
                $nuevos->push($logro);
            }
        }

        return $nuevos;
    }
}
