<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = [User::ROL_ADMIN, User::ROL_DOCENTE, User::ROL_ALUMNO];

    public function index(Request $request): View
    {
        $usuarios = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->trim();
                $query->where(fn ($w) => $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%"));
            })
            ->when(in_array($request->input('rol'), self::ROLES, true),
                fn ($query) => $query->where('rol', $request->input('rol')))
            ->when($request->input('estado') === 'activos', fn ($query) => $query->where('activo', true))
            ->when($request->input('estado') === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', ['usuarios' => $usuarios]);
    }

    public function create(): View
    {
        return view('admin.usuarios.create', ['usuario' => new User(['rol' => User::ROL_ALUMNO, 'activo' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'rol' => ['required', Rule::in(self::ROLES)],
        ]);

        $usuario = User::create($datos + ['activo' => $request->boolean('activo')]);
        // Las cuentas creadas por el administrador quedan verificadas
        $usuario->forceFill(['email_verified_at' => now()])->save();

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        return view('admin.usuarios.edit', ['usuario' => $usuario]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', Password::defaults()],
            'rol' => ['required', Rule::in(self::ROLES)],
        ]);

        $activo = $request->boolean('activo');

        // El administrador no puede quitarse a sí mismo el rol ni el acceso
        if ($usuario->is($request->user()) && ($datos['rol'] !== $usuario->rol || ! $activo)) {
            return back()->withInput()->withErrors([
                'rol' => 'No puedes cambiar tu propio rol ni desactivar tu propia cuenta.',
            ]);
        }

        if (empty($datos['password'])) {
            unset($datos['password']);
        }

        // Si cambia el correo, debe verificarse de nuevo
        if ($datos['email'] !== $usuario->email) {
            $usuario->email_verified_at = null;
        }

        $usuario->fill($datos + ['activo' => $activo])->save();

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')->with('status', 'Usuario eliminado.');
    }

    // Activar / desactivar cuenta
    public function estado(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->is($request->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('status', $usuario->activo ? 'Cuenta activada.' : 'Cuenta desactivada.');
    }

    // Verificación manual de correo (contingencia DRP-05)
    public function verificar(User $usuario): RedirectResponse
    {
        if (! $usuario->email_verified_at) {
            $usuario->forceFill(['email_verified_at' => now()])->save();
        }

        return back()->with('status', 'Cuenta verificada manualmente.');
    }
}
