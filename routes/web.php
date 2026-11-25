<?php

use App\Http\Controllers\Admin\EjercicioController;
use App\Http\Controllers\Admin\LeccionController;
use App\Http\Controllers\Admin\UnidadController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Docente\GrupoController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RolMiddleware;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Después de iniciar sesión, cada usuario va a su panel según su rol
Route::get('/dashboard', function () {
    return match (auth()->user()->rol) {
        User::ROL_ADMIN => redirect()->route('admin.panel'),
        User::ROL_DOCENTE => redirect()->route('docente.panel'),
        default => redirect()->route('alumno.panel'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Perfil: común a los tres roles
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Administrador
Route::middleware(['auth', 'verified', RolMiddleware::class.':admin'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('admin.panel', [
                'totalAdmins' => User::where('rol', User::ROL_ADMIN)->count(),
                'totalDocentes' => User::where('rol', User::ROL_DOCENTE)->count(),
                'totalAlumnos' => User::where('rol', User::ROL_ALUMNO)->count(),
            ]);
        })->name('panel');

        // Gestión de usuarios y docentes (RF006, RF007)
        Route::resource('usuarios', UserController::class)
            ->parameters(['usuarios' => 'usuario'])
            ->except(['show']);
        Route::patch('usuarios/{usuario}/estado', [UserController::class, 'estado'])->name('usuarios.estado');
        Route::patch('usuarios/{usuario}/verificar', [UserController::class, 'verificar'])->name('usuarios.verificar');

        // Contenido: unidades, lecciones y ejercicios (RF008, RF009)
        Route::resource('unidades', UnidadController::class)->parameters(['unidades' => 'unidad']);
        Route::patch('unidades/{unidad}/publicar', [UnidadController::class, 'publicar'])->name('unidades.publicar');

        Route::resource('unidades.lecciones', LeccionController::class)
            ->shallow()->except(['index'])
            ->parameters(['unidades' => 'unidad', 'lecciones' => 'leccion']);

        Route::resource('lecciones.ejercicios', EjercicioController::class)
            ->shallow()->except(['index', 'show'])
            ->parameters(['lecciones' => 'leccion', 'ejercicios' => 'ejercicio']);
        Route::patch('ejercicios/{ejercicio}/publicar', [EjercicioController::class, 'publicar'])->name('ejercicios.publicar');
    });

// Docente
Route::middleware(['auth', 'verified', RolMiddleware::class.':docente'])
    ->prefix('docente')->name('docente.')->group(function () {
        Route::get('/', function () {
            $grupos = auth()->user()->gruposCreados()->withCount('alumnos')->orderBy('nombre')->get();

            return view('docente.panel', ['grupos' => $grupos]);
        })->name('panel');

        // Mis grupos (RF010)
        Route::resource('grupos', GrupoController::class);
        Route::post('grupos/{grupo}/alumnos', [GrupoController::class, 'agregarAlumno'])->name('grupos.alumnos.agregar');
        Route::delete('grupos/{grupo}/alumnos/{alumno}', [GrupoController::class, 'quitarAlumno'])->name('grupos.alumnos.quitar');
    });

// Alumno
Route::middleware(['auth', 'verified', RolMiddleware::class.':alumno'])
    ->prefix('alumno')->name('alumno.')->group(function () {
        Route::get('/', function () {
            return view('alumno.panel');
        })->name('panel');
    });

require __DIR__.'/auth.php';
