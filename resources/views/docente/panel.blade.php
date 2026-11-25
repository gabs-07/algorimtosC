<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel del docente</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-900">Hola, {{ auth()->user()->nombre }}. Aquí administrarás tus grupos y verás el avance de tus alumnos.</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Mis grupos</h3>
                    <a href="{{ route('docente.grupos.index') }}" class="text-sm text-indigo-600 hover:underline">Administrar grupos</a>
                </div>
                @forelse ($grupos as $grupo)
                    <a href="{{ route('docente.grupos.show', $grupo) }}" class="flex justify-between border-b py-2 hover:bg-gray-50">
                        <span>{{ $grupo->nombre }}</span>
                        <span class="text-gray-500">{{ $grupo->alumnos_count }} alumnos</span>
                    </a>
                @empty
                    <p class="text-gray-500">Todavía no tienes grupos.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
