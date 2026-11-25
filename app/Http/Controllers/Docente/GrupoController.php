<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Desbloqueo;
use App\Models\Ejercicio;
use App\Models\Grupo;
use App\Models\Intento;
use App\Models\Unidad;
use App\Models\User;
use App\Services\RutaAprendizaje;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrupoController extends Controller
{
    public function __construct(private RutaAprendizaje $ruta)
    {
    }

    // Un docente solo puede ver y modificar sus propios grupos (RF005, RF010)
    private function propio(Grupo $grupo): void
    {
        abort_unless($grupo->docente_id === auth()->id(), 403, 'Este grupo no te pertenece.');
    }

    private function reglasNombre(?Grupo $grupo = null): array
    {
        return [
            'required', 'string', 'max:100',
            Rule::unique('grupos', 'nombre')
                ->where('docente_id', auth()->id())
                ->ignore($grupo?->id),
        ];
    }

    public function index(): View
    {
        $grupos = auth()->user()->gruposCreados()
            ->withCount('alumnos')
            ->orderBy('nombre')
            ->get();

        return view('docente.grupos.index', ['grupos' => $grupos]);
    }

    public function create(): View
    {
        return view('docente.grupos.create', ['grupo' => new Grupo()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate(
            ['nombre' => $this->reglasNombre()],
            ['nombre.unique' => 'Ya tienes un grupo con ese nombre.']
        );

        $grupo = auth()->user()->gruposCreados()->create($datos);

        return redirect()->route('docente.grupos.show', $grupo)->with('status', 'Grupo creado. Ahora puedes agregar alumnos.');
    }

    public function show(Grupo $grupo): View
    {
        $this->propio($grupo);

        $alumnos = $grupo->alumnos()->orderBy('nombre')->get();
        $alumnoIds = $alumnos->pluck('id');

        // Avance de cada alumno por unidad (RF021)
        $matriz = $alumnos->mapWithKeys(fn ($a) => [
            $a->id => $this->ruta->estados($a)->keyBy(fn ($i) => $i['unidad']->id),
        ]);

        return view('docente.grupos.show', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'unidades' => Unidad::where('publicada', true)->orderBy('orden')->orderBy('id')->get(),
            'matriz' => $matriz,
            'errores' => $this->ejerciciosConMasErrores($alumnoIds),
            'desbloqueos' => Desbloqueo::with(['alumno', 'unidad'])
                ->whereIn('alumno_id', $alumnoIds)
                ->where('docente_id', auth()->id())
                ->orderByDesc('created_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function edit(Grupo $grupo): View
    {
        $this->propio($grupo);

        return view('docente.grupos.edit', ['grupo' => $grupo]);
    }

    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $this->propio($grupo);

        $datos = $request->validate(
            ['nombre' => $this->reglasNombre($grupo)],
            ['nombre.unique' => 'Ya tienes un grupo con ese nombre.']
        );

        $grupo->update($datos);

        return redirect()->route('docente.grupos.show', $grupo)->with('status', 'Grupo actualizado.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        $this->propio($grupo);

        $grupo->delete();

        return redirect()->route('docente.grupos.index')->with('status', 'Grupo eliminado.');
    }

    // Agregar un alumno al grupo por su correo
    public function agregarAlumno(Request $request, Grupo $grupo): RedirectResponse
    {
        $this->propio($grupo);

        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Escribe el correo del alumno.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);

        $alumno = User::where('email', strtolower($request->input('email')))
            ->where('rol', User::ROL_ALUMNO)
            ->where('activo', true)
            ->first();

        if (! $alumno) {
            return back()->withInput()->withErrors(['email' => 'No existe un alumno activo con ese correo.']);
        }

        if ($grupo->alumnos()->whereKey($alumno->id)->exists()) {
            return back()->withInput()->withErrors(['email' => 'Ese alumno ya está en el grupo.']);
        }

        $grupo->alumnos()->attach($alumno->id, ['created_at' => now()]);

        return back()->with('status', "{$alumno->nombre} se agregó al grupo.");
    }

    public function quitarAlumno(Grupo $grupo, User $alumno): RedirectResponse
    {
        $this->propio($grupo);

        $grupo->alumnos()->detach($alumno->id);

        return back()->with('status', 'Alumno quitado del grupo.');
    }

    // Desbloqueo manual de una unidad para alumnos del grupo (RF011)
    public function desbloquear(Request $request, Grupo $grupo): RedirectResponse
    {
        $this->propio($grupo);

        $request->validate([
            'unidad_id' => ['required', 'integer', Rule::exists('unidades', 'id')->where('publicada', true)],
            'alumnos' => ['nullable', 'array'],
            'alumnos.*' => ['integer'],
        ], [
            'unidad_id.required' => 'Elige la unidad que quieres desbloquear.',
            'unidad_id.exists' => 'La unidad elegida no existe o no está publicada.',
        ]);

        // Solo alumnos que pertenecen a este grupo
        $idsGrupo = $grupo->alumnos()->pluck('users.id');
        $seleccion = $request->boolean('todos')
            ? $idsGrupo
            : $idsGrupo->intersect(collect($request->input('alumnos', []))->map(fn ($i) => (int) $i));

        if ($seleccion->isEmpty()) {
            return back()->withErrors(['alumnos' => 'Selecciona al menos un alumno o marca "Todo el grupo".'])->withInput();
        }

        $unidad = Unidad::findOrFail($request->input('unidad_id'));
        $desbloqueados = 0;
        $yaDisponibles = 0;

        foreach (User::whereIn('id', $seleccion)->get() as $alumno) {
            if (($this->ruta->estado($alumno, $unidad)['estado'] ?? null) !== RutaAprendizaje::BLOQUEADA) {
                $yaDisponibles++;
                continue;
            }

            // Queda registrado: quién, cuándo y a quién
            Desbloqueo::firstOrCreate(
                ['alumno_id' => $alumno->id, 'unidad_id' => $unidad->id],
                ['docente_id' => auth()->id()]
            );
            $desbloqueados++;
        }

        $mensaje = "Unidad «{$unidad->titulo}» desbloqueada para {$desbloqueados} alumno(s).";
        if ($yaDisponibles > 0) {
            $mensaje .= " {$yaDisponibles} ya la tenían disponible.";
        }

        return back()->with('status', $mensaje);
    }

    /** Los 5 ejercicios en los que más se equivocan los alumnos del grupo */
    private function ejerciciosConMasErrores(Collection $alumnoIds): Collection
    {
        if ($alumnoIds->isEmpty()) {
            return collect();
        }

        $filas = Intento::query()
            ->whereIn('alumno_id', $alumnoIds)
            ->selectRaw('ejercicio_id, COUNT(*) as total, SUM(CASE WHEN es_correcto = 0 THEN 1 ELSE 0 END) as errores')
            ->groupBy('ejercicio_id')
            ->havingRaw('SUM(CASE WHEN es_correcto = 0 THEN 1 ELSE 0 END) > 0')
            ->orderByDesc('errores')
            ->limit(5)
            ->get();

        $ejercicios = Ejercicio::with('leccion.unidad')->whereIn('id', $filas->pluck('ejercicio_id'))->get()->keyBy('id');

        return $filas
            ->filter(fn ($f) => $ejercicios->has($f->ejercicio_id))
            ->map(fn ($f) => [
                'ejercicio' => $ejercicios[$f->ejercicio_id],
                'total' => (int) $f->total,
                'errores' => (int) $f->errores,
                'porcentaje' => (int) round($f->errores * 100 / $f->total),
            ])
            ->values();
    }
}
