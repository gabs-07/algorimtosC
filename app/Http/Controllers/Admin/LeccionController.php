<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leccion;
use App\Models\Unidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeccionController extends Controller
{
    private function reglas(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:150'],
            'contenido' => ['required', 'string'],
            'orden' => ['required', 'integer', 'min:1'],
        ];
    }

    public function create(Unidad $unidad): View
    {
        $leccion = new Leccion([
            'unidad_id' => $unidad->id,
            'orden' => (Leccion::where('unidad_id', $unidad->id)->max('orden') ?? 0) + 1,
        ]);

        return view('admin.lecciones.create', ['unidad' => $unidad, 'leccion' => $leccion]);
    }

    public function store(Request $request, Unidad $unidad): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        $leccion = Leccion::create($datos + ['unidad_id' => $unidad->id]);

        return redirect()->route('admin.lecciones.show', $leccion)->with('status', 'Lección creada. Ahora agrega sus ejercicios.');
    }

    public function show(Leccion $leccion): View
    {
        return view('admin.lecciones.show', [
            'leccion' => $leccion->load('unidad'),
            'ejercicios' => $leccion->ejercicios()->get(),
        ]);
    }

    public function edit(Leccion $leccion): View
    {
        return view('admin.lecciones.edit', ['leccion' => $leccion->load('unidad'), 'unidad' => $leccion->unidad]);
    }

    public function update(Request $request, Leccion $leccion): RedirectResponse
    {
        $leccion->update($request->validate($this->reglas()));

        return redirect()->route('admin.lecciones.show', $leccion)->with('status', 'Lección actualizada.');
    }

    public function destroy(Leccion $leccion): RedirectResponse
    {
        $unidad = $leccion->unidad;
        $leccion->delete();

        return redirect()->route('admin.unidades.show', $unidad)->with('status', 'Lección eliminada junto con sus ejercicios.');
    }
}
