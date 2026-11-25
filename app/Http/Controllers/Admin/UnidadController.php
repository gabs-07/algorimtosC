<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UnidadController extends Controller
{
    private function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'orden' => ['required', 'integer', 'min:1'],
        ];
    }

    public function index(): View
    {
        $unidades = Unidad::withCount('lecciones')->orderBy('orden')->get();

        return view('admin.unidades.index', ['unidades' => $unidades]);
    }

    public function create(): View
    {
        $unidad = new Unidad(['orden' => (Unidad::max('orden') ?? 0) + 1, 'publicada' => false]);

        return view('admin.unidades.create', ['unidad' => $unidad]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        $unidad = Unidad::create($datos + ['publicada' => $request->boolean('publicada')]);

        return redirect()->route('admin.unidades.show', $unidad)->with('status', 'Unidad creada. Ahora agrega sus lecciones.');
    }

    public function show(Unidad $unidad): View
    {
        return view('admin.unidades.show', [
            'unidad' => $unidad,
            'lecciones' => $unidad->lecciones()->withCount('ejercicios')->get(),
        ]);
    }

    public function edit(Unidad $unidad): View
    {
        return view('admin.unidades.edit', ['unidad' => $unidad]);
    }

    public function update(Request $request, Unidad $unidad): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        $unidad->update($datos + ['publicada' => $request->boolean('publicada')]);

        return redirect()->route('admin.unidades.show', $unidad)->with('status', 'Unidad actualizada.');
    }

    public function destroy(Unidad $unidad): RedirectResponse
    {
        $unidad->delete();

        return redirect()->route('admin.unidades.index')->with('status', 'Unidad eliminada junto con sus lecciones y ejercicios.');
    }

    public function publicar(Unidad $unidad): RedirectResponse
    {
        $unidad->update(['publicada' => ! $unidad->publicada]);

        return back()->with('status', $unidad->publicada ? 'Unidad publicada.' : 'Unidad ocultada a los alumnos.');
    }
}
