<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Models\Ejercicio;
use App\Models\Intento;
use App\Models\Leccion;
use App\Models\Unidad;
use App\Services\RutaAprendizaje;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RutaController extends Controller
{
    public function __construct(private RutaAprendizaje $ruta)
    {
    }

    /** Mi ruta: todas las unidades con su estado */
    public function index(): View
    {
        return view('alumno.panel', ['items' => $this->ruta->estados(auth()->user())]);
    }

    public function unidad(Unidad $unidad): View
    {
        $this->autorizar($unidad);

        $lecciones = $unidad->lecciones()
            ->with(['ejercicios' => fn ($q) => $q->where('publicado', true)])
            ->get();

        return view('alumno.unidad', [
            'unidad' => $unidad,
            'lecciones' => $lecciones,
            'resueltos' => $this->ruta->idsResueltos(auth()->user()),
        ]);
    }

    public function leccion(Leccion $leccion): View
    {
        $leccion->load('unidad');
        $this->autorizar($leccion->unidad);

        return view('alumno.leccion', [
            'leccion' => $leccion,
            'unidad' => $leccion->unidad,
            'ejercicios' => $leccion->ejercicios()->where('publicado', true)->get(),
            'resueltos' => $this->ruta->idsResueltos(auth()->user()),
        ]);
    }

    public function ejercicio(Ejercicio $ejercicio): View
    {
        $leccion = $this->cargarYAutorizar($ejercicio);
        $opciones = $ejercicio->opciones()->get();

        $datos = ['opciones' => [], 'lineas' => [], 'antes' => null, 'despues' => null];

        if ($ejercicio->tipo === 'opcion_multiple') {
            // Las opciones se barajan en cada visita (RF014)
            $datos['opciones'] = $opciones->shuffle()->map(fn ($o) => ['id' => $o->id, 'texto' => $o->texto])->values()->all();
        } elseif ($ejercicio->tipo === 'ordenar') {
            $correcto = $opciones->sortBy('posicion_correcta')->pluck('id')->values()->all();
            $barajadas = $opciones->shuffle()->values();
            for ($i = 0; $i < 10 && $opciones->count() > 1 && $barajadas->pluck('id')->values()->all() === $correcto; $i++) {
                $barajadas = $opciones->shuffle()->values();
            }
            $datos['lineas'] = $barajadas->map(fn ($o) => ['id' => $o->id, 'texto' => $o->texto])->all();
        } else {
            [$antes, $despues] = array_pad(explode('___', (string) $ejercicio->codigo_base, 2), 2, '');
            $datos['antes'] = $antes;
            $datos['despues'] = $despues;
        }

        $resultado = session('resultado');

        return view('alumno.ejercicio', $datos + [
            'ejercicio' => $ejercicio,
            'leccion' => $leccion,
            'resultado' => $resultado,
            'siguiente' => ($resultado['correcto'] ?? false) ? $this->siguiente($ejercicio) : null,
            'yaResuelto' => $this->ruta->idsResueltos(auth()->user())->has($ejercicio->id),
        ]);
    }

    public function responder(Request $request, Ejercicio $ejercicio): RedirectResponse
    {
        $leccion = $this->cargarYAutorizar($ejercicio);
        $alumno = $request->user();
        $ejercicio->load('opciones');

        [$respuesta, $correcto] = match ($ejercicio->tipo) {
            'opcion_multiple' => $this->evaluarOpcionMultiple($request, $ejercicio),
            'completar' => $this->evaluarCompletar($request, $ejercicio),
            'ordenar' => $this->evaluarOrdenar($request, $ejercicio),
        };

        $estadoAntes = $this->ruta->estado($alumno, $leccion->unidad)['estado'] ?? null;

        // Cada envío queda registrado (RF017)
        Intento::create([
            'alumno_id' => $alumno->id,
            'ejercicio_id' => $ejercicio->id,
            'respuesta' => $respuesta,
            'es_correcto' => $correcto,
        ]);

        $unidadCompletada = null;
        if ($correcto) {
            $estadoDespues = $this->ruta->estado($alumno, $leccion->unidad)['estado'] ?? null;
            if ($estadoAntes !== RutaAprendizaje::COMPLETADA && $estadoDespues === RutaAprendizaje::COMPLETADA) {
                $unidadCompletada = $leccion->unidad->titulo;
            }
        }

        return redirect()
            ->route('alumno.ejercicios.show', $ejercicio)
            ->withInput($ejercicio->tipo === 'completar' ? $request->only('respuesta') : [])
            ->with('resultado', ['correcto' => $correcto, 'unidad_completada' => $unidadCompletada]);
    }

    /* ---------------- Evaluación por tipo ---------------- */

    private function evaluarOpcionMultiple(Request $request, Ejercicio $ejercicio): array
    {
        $request->validate(['opcion' => ['required', 'integer']], ['opcion.required' => 'Elige una opción.']);

        $opcion = $ejercicio->opciones->firstWhere('id', (int) $request->input('opcion'));
        abort_unless($opcion, 422, 'Opción no válida.');

        return [$opcion->texto, (bool) $opcion->es_correcta];
    }

    private function evaluarCompletar(Request $request, Ejercicio $ejercicio): array
    {
        $request->validate(
            ['respuesta' => ['required', 'string', 'max:500']],
            ['respuesta.required' => 'Escribe tu respuesta en el espacio resaltado.']
        );

        $respuesta = (string) $request->input('respuesta');

        // Se ignoran las diferencias de espacios (RF015)
        $correcto = $ejercicio->opciones->contains(
            fn ($o) => $this->normalizar($o->texto) === $this->normalizar($respuesta)
        );

        return [$respuesta, $correcto];
    }

    private function evaluarOrdenar(Request $request, Ejercicio $ejercicio): array
    {
        $request->validate(
            ['orden' => ['required', 'string', 'regex:/^\d+(,\d+)*$/']],
            ['orden.required' => 'Ordena las líneas antes de enviar.', 'orden.regex' => 'El orden enviado no es válido.']
        );

        $ids = array_map('intval', explode(',', $request->input('orden')));
        $porId = $ejercicio->opciones->keyBy('id');

        abort_unless(
            count($ids) === $porId->count() && count(array_unique($ids)) === count($ids) && collect($ids)->every(fn ($id) => $porId->has($id)),
            422,
            'El orden enviado no corresponde a este ejercicio.'
        );

        $enviado = array_map(fn ($id) => rtrim($porId[$id]->texto), $ids);
        $esperado = $ejercicio->opciones->sortBy('posicion_correcta')->map(fn ($o) => rtrim($o->texto))->values()->all();

        // Se comparan los textos (y no los ids) para que líneas repetidas, como "}", no causen errores
        return [implode("\n", $enviado), $enviado === $esperado];
    }

    private function normalizar(string $texto): string
    {
        return preg_replace('/\s+/', '', $texto);
    }

    /* ---------------- Utilidades ---------------- */

    private function autorizar(Unidad $unidad): void
    {
        abort_unless($unidad->publicada, 404);
        abort_unless(
            $this->ruta->puedeAcceder(auth()->user(), $unidad),
            403,
            'Esta unidad está bloqueada. Completa la unidad anterior para desbloquearla.'
        );
    }

    /** Verifica que el ejercicio esté publicado y su unidad sea accesible. Devuelve la lección. */
    private function cargarYAutorizar(Ejercicio $ejercicio): Leccion
    {
        abort_unless($ejercicio->publicado, 404);

        $leccion = $ejercicio->leccion()->with('unidad')->firstOrFail();
        $this->autorizar($leccion->unidad);

        return $leccion;
    }

    private function siguiente(Ejercicio $actual): ?Ejercicio
    {
        $enLaLeccion = Ejercicio::where('leccion_id', $actual->leccion_id)
            ->where('publicado', true)
            ->where('orden', '>', $actual->orden)
            ->orderBy('orden')
            ->first();

        if ($enLaLeccion) {
            return $enLaLeccion;
        }

        $leccionActual = $actual->leccion;
        $lecciones = Leccion::where('unidad_id', $leccionActual->unidad_id)
            ->where('orden', '>', $leccionActual->orden)
            ->orderBy('orden')
            ->get();

        foreach ($lecciones as $leccion) {
            $ejercicio = Ejercicio::where('leccion_id', $leccion->id)->where('publicado', true)->orderBy('orden')->first();
            if ($ejercicio) {
                return $ejercicio;
            }
        }

        return null;
    }
}
