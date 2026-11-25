<?php

namespace App\Services;

use App\Models\Desbloqueo;
use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Calcula el estado de cada unidad para un alumno (RF012, RF013, RF021).
 *
 * - La primera unidad publicada está disponible desde el inicio.
 * - Una unidad se desbloquea cuando la anterior está completada,
 *   o cuando un docente la desbloqueó manualmente (tabla desbloqueos).
 * - Una unidad está completada cuando el alumno resolvió correctamente
 *   todos sus ejercicios publicados.
 */
class RutaAprendizaje
{
    public const BLOQUEADA = 'bloqueada';
    public const DISPONIBLE = 'disponible';
    public const COMPLETADA = 'completada';

    /** @return Collection<int, array{unidad: Unidad, estado: string, total: int, resueltos: int, porcentaje: int}> */
    public function estados(User $alumno): Collection
    {
        $unidades = Unidad::where('publicada', true)->orderBy('orden')->orderBy('id')->get();

        $ejerciciosPorUnidad = Ejercicio::query()
            ->join('lecciones', 'lecciones.id', '=', 'ejercicios.leccion_id')
            ->where('ejercicios.publicado', true)
            ->get(['ejercicios.id as id', 'lecciones.unidad_id as unidad_id'])
            ->groupBy('unidad_id');

        $correctos = $this->idsResueltos($alumno);

        $desbloqueadas = Desbloqueo::where('alumno_id', $alumno->id)->pluck('unidad_id')->flip();

        $resultado = collect();
        $anteriorCompleta = true; // la primera unidad siempre está disponible

        foreach ($unidades as $unidad) {
            $ids = $ejerciciosPorUnidad->get($unidad->id, collect())->pluck('id');
            $total = $ids->count();
            $resueltos = $ids->filter(fn ($id) => $correctos->has($id))->count();

            $disponible = $anteriorCompleta || $desbloqueadas->has($unidad->id);
            $completa = $total === 0 || $resueltos === $total;

            $estado = ! $disponible
                ? self::BLOQUEADA
                : ($completa ? self::COMPLETADA : self::DISPONIBLE);

            $resultado->push([
                'unidad' => $unidad,
                'estado' => $estado,
                'total' => $total,
                'resueltos' => $resueltos,
                'porcentaje' => $total > 0 ? (int) round($resueltos * 100 / $total) : ($disponible ? 100 : 0),
            ]);

            $anteriorCompleta = $estado === self::COMPLETADA;
        }

        return $resultado;
    }

    /** @return array{unidad: Unidad, estado: string, total: int, resueltos: int, porcentaje: int}|null */
    public function estado(User $alumno, Unidad $unidad): ?array
    {
        return $this->estados($alumno)->first(fn ($item) => $item['unidad']->id === $unidad->id);
    }

    /** ¿Puede el alumno abrir esta unidad? (RF013: tampoco por URL directa) */
    public function puedeAcceder(User $alumno, Unidad $unidad): bool
    {
        if (! $unidad->publicada) {
            return false;
        }

        $item = $this->estado($alumno, $unidad);

        return $item !== null && $item['estado'] !== self::BLOQUEADA;
    }

    /** ids de ejercicios que el alumno ya resolvió bien (las llaves son los ids) */
    public function idsResueltos(User $alumno): Collection
    {
        return Intento::where('alumno_id', $alumno->id)
            ->where('es_correcto', true)
            ->distinct()
            ->pluck('ejercicio_id')
            ->flip();
    }
}
