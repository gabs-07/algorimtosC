<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GrupoController extends Controller
{
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

        return view('docente.grupos.show', [
            'grupo' => $grupo,
            'alumnos' => $grupo->alumnos()->orderBy('nombre')->get(),
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
}
