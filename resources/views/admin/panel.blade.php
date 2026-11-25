<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de administración</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-900">Hola, {{ auth()->user()->nombre }}. Desde aquí gestionarás usuarios, docentes, unidades, lecciones y ejercicios.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <a href="{{ route('admin.usuarios.index', ['rol' => 'admin']) }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Administradores</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalAdmins }}</p>
                </a>
                <a href="{{ route('admin.usuarios.index', ['rol' => 'docente']) }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Docentes</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalDocentes }}</p>
                </a>
                <a href="{{ route('admin.usuarios.index', ['rol' => 'alumno']) }}" class="bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <p class="text-sm text-gray-500">Alumnos</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalAlumnos }}</p>
                </a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-wrap gap-3">
                <a href="{{ route('admin.unidades.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-500">Contenido: unidades y lecciones</a>
                <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Gestionar usuarios</a>
                <a href="{{ route('admin.usuarios.index', ['rol' => 'docente']) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Gestionar docentes</a>
                <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-50">Nuevo usuario</a>
            </div>
        </div>
    </div>
</x-app-layout>
