<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\Leccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EjercicioController extends Controller
{
    public function create(Leccion $leccion): View
    {
        $ejercicio = new Ejercicio([
            'leccion_id' => $leccion->id,
            'tipo' => 'opcion_multiple',
            'orden' => (Ejercicio::where('leccion_id', $leccion->id)->max('orden') ?? 0) + 1,
            'publicado' => true,
        ]);

        return view('admin.ejercicios.create', [
            'leccion' => $leccion,
            'ejercicio' => $ejercicio,
            'datos' => $this->datosFormulario($ejercicio),
        ]);
    }

    public function store(Request $request, Leccion $leccion): RedirectResponse
    {
        $request->validate(['tipo' => ['required', Rule::in(Ejercicio::TIPOS)]]);

        $ejercicio = new Ejercicio(['leccion_id' => $leccion->id, 'tipo' => $request->input('tipo')]);
        $this->guardar($request, $ejercicio);

        return redirect()->route('admin.lecciones.show', $leccion)->with('status', 'Ejercicio creado.');
    }

    public function edit(Ejercicio $ejercicio): View
    {
        return view('admin.ejercicios.edit', [
            'leccion' => $ejercicio->leccion,
            'ejercicio' => $ejercicio,
            'datos' => $this->datosFormulario($ejercicio),
        ]);
    }

    public function update(Request $request, Ejercicio $ejercicio): RedirectResponse
    {
        // El tipo no se puede cambiar una vez creado el ejercicio
        $this->guardar($request, $ejercicio);

        return redirect()->route('admin.lecciones.show', $ejercicio->leccion_id)->with('status', 'Ejercicio actualizado.');
    }

    public function destroy(Ejercicio $ejercicio): RedirectResponse
    {
        $leccionId = $ejercicio->leccion_id;
        $ejercicio->delete();

        return redirect()->route('admin.lecciones.show', $leccionId)->with('status', 'Ejercicio eliminado.');
    }

    public function publicar(Ejercicio $ejercicio): RedirectResponse
    {
        // No se puede publicar un ejercicio sin respuesta correcta (RF009)
        if (! $ejercicio->publicado && ! $this->tieneRespuestaCorrecta($ejercicio)) {
            return back()->with('error', 'Este ejercicio no tiene respuesta correcta, no se puede publicar.');
        }

        $ejercicio->update(['publicado' => ! $ejercicio->publicado]);

        return back()->with('status', $ejercicio->publicado ? 'Ejercicio publicado.' : 'Ejercicio ocultado a los alumnos.');
    }

    private function tieneRespuestaCorrecta(Ejercicio $ejercicio): bool
    {
        $opciones = $ejercicio->opciones;

        return match ($ejercicio->tipo) {
            'opcion_multiple' => $opciones->count() >= 2 && $opciones->where('es_correcta', true)->count() === 1,
            'completar' => $opciones->where('es_correcta', true)->isNotEmpty(),
            'ordenar' => $opciones->count() >= 2 && $opciones->whereNotNull('posicion_correcta')->count() === $opciones->count(),
            default => false,
        };
    }

    /** Valores que se muestran en el formulario según el tipo de ejercicio */
    private function datosFormulario(Ejercicio $e): array
    {
        $d = ['opciones' => ['', '', '', ''], 'correcta' => 0, 'soluciones' => '', 'lineas' => ''];

        if (! $e->exists) {
            return $d;
        }

        $opciones = $e->opciones()->orderBy('id')->get();

        if ($e->tipo === 'opcion_multiple') {
            foreach ($opciones->values() as $i => $o) {
                if ($i < 4) {
                    $d['opciones'][$i] = $o->texto;
                    if ($o->es_correcta) {
                        $d['correcta'] = $i;
                    }
                }
            }
        } elseif ($e->tipo === 'completar') {
            $d['soluciones'] = $opciones->pluck('texto')->implode("\n");
        } else {
            $d['lineas'] = $opciones->sortBy('posicion_correcta')->pluck('texto')->implode("\n");
        }

        return $d;
    }

    /** Valida según el tipo y guarda el ejercicio con sus opciones */
    private function guardar(Request $request, Ejercicio $ejercicio): void
    {
        $base = $request->validate([
            'enunciado' => ['required', 'string'],
            'codigo_base' => ['nullable', 'string'],
            'orden' => ['required', 'integer', 'min:1'],
        ]);

        $opciones = match ($ejercicio->tipo) {
            'opcion_multiple' => $this->opcionesMultiple($request),
            'completar' => $this->opcionesCompletar($request),
            'ordenar' => $this->opcionesOrdenar($request),
        };

        DB::transaction(function () use ($ejercicio, $base, $opciones, $request) {
            $ejercicio->fill($base + ['publicado' => $request->boolean('publicado')])->save();
            $ejercicio->opciones()->delete();
            $ejercicio->opciones()->createMany($opciones);
        });
    }

    private function opcionesMultiple(Request $request): array
    {
        $textos = array_map(fn ($t) => trim((string) $t), (array) $request->input('opciones', []));
        $llenas = array_filter($textos, fn ($t) => $t !== '');
        $correcta = (int) $request->input('correcta', -1);

        if (count($llenas) < 2) {
            throw ValidationException::withMessages(['opciones' => 'Escribe al menos 2 opciones.']);
        }
        if (! isset($llenas[$correcta])) {
            throw ValidationException::withMessages(['correcta' => 'Marca como correcta una opción que no esté vacía.']);
        }

        $resultado = [];
        foreach ($llenas as $i => $texto) {
            $resultado[] = ['texto' => $texto, 'es_correcta' => $i === $correcta, 'posicion_correcta' => null];
        }

        return $resultado;
    }

    private function opcionesCompletar(Request $request): array
    {
        if (! str_contains((string) $request->input('codigo_base'), '___')) {
            throw ValidationException::withMessages([
                'codigo_base' => 'El código debe incluir el espacio a completar, marcado con ___ (tres guiones bajos).',
            ]);
        }

        $soluciones = array_values(array_filter(
            array_map('trim', preg_split('/\R/', (string) $request->input('soluciones'))),
            fn ($s) => $s !== ''
        ));

        if (! $soluciones) {
            throw ValidationException::withMessages(['soluciones' => 'Escribe al menos una respuesta válida.']);
        }

        return array_map(fn ($s) => ['texto' => $s, 'es_correcta' => true, 'posicion_correcta' => null], $soluciones);
    }

    private function opcionesOrdenar(Request $request): array
    {
        $lineas = array_values(array_filter(
            array_map('rtrim', preg_split('/\R/', (string) $request->input('lineas'))),
            fn ($l) => trim($l) !== ''
        ));

        if (count($lineas) < 2) {
            throw ValidationException::withMessages(['lineas' => 'Escribe al menos 2 líneas de código.']);
        }

        $resultado = [];
        foreach ($lineas as $i => $linea) {
            $resultado[] = ['texto' => $linea, 'es_correcta' => null, 'posicion_correcta' => $i + 1];
        }

        return $resultado;
    }
}
