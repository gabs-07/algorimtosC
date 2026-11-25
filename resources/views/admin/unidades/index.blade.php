<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Unidades y lecciones</h2>
            <a href="{{ route('admin.unidades.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nueva unidad</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            @forelse ($unidades as $unidad)
                <div class="bg-white shadow-sm sm:rounded-lg">
                    {{-- Encabezado de la unidad --}}
                    <div class="flex flex-wrap justify-between items-center gap-3 p-4 border-b">
                        <div>
                            <a href="{{ route('admin.unidades.show', $unidad) }}" class="font-semibold text-lg text-indigo-600 hover:underline">
                                {{ $unidad->orden }}. {{ $unidad->titulo }}
                            </a>
                            <p class="text-sm text-gray-500">
                                {{ $unidad->lecciones_count }} lecciones ·
                                <span class="{{ $unidad->publicada ? 'text-green-700' : 'text-yellow-700' }}">{{ $unidad->publicada ? 'Publicada' : 'Borrador' }}</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-4 text-sm">
                            <form method="POST" action="{{ route('admin.unidades.publicar', $unidad) }}">
                                @csrf @method('PATCH')
                                <button class="text-yellow-700 hover:underline">{{ $unidad->publicada ? 'Ocultar' : 'Publicar' }}</button>
                            </form>
                            <a href="{{ route('admin.unidades.edit', $unidad) }}" class="text-indigo-600 hover:underline">Editar</a>
                            <form method="POST" action="{{ route('admin.unidades.destroy', $unidad) }}" onsubmit="return confirm('¿Eliminar la unidad y TODAS sus lecciones, ejercicios e intentos de los alumnos? No se puede deshacer.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </div>
                    </div>

                    {{-- Lecciones de la unidad --}}
                    <div class="divide-y">
                        @forelse ($unidad->lecciones as $leccion)
                            <div class="flex justify-between items-center px-4 py-3">
                                <a href="{{ route('admin.lecciones.show', $leccion) }}" class="text-gray-800 hover:text-indigo-600 hover:underline">
                                    {{ $leccion->orden }}. {{ $leccion->titulo }}
                                </a>
                                <div class="flex gap-4 text-sm">
                                    <a href="{{ route('admin.lecciones.show', $leccion) }}" class="text-indigo-600 hover:underline">Ejercicios</a>
                                    <a href="{{ route('admin.lecciones.edit', $leccion) }}" class="text-indigo-600 hover:underline">Editar</a>
                                </div>
                            </div>
                        @empty
                            <p class="px-4 py-3 text-sm text-gray-500">Esta unidad todavía no tiene lecciones.</p>
                        @endforelse
                    </div>

                    <div class="p-4 border-t bg-gray-50 sm:rounded-b-lg">
                        <a href="{{ route('admin.unidades.lecciones.create', $unidad) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-500">+ Agregar lección</a>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Todavía no hay unidades. Crea la primera con el botón "Nueva unidad".
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
