<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Unidades</h2>
            <a href="{{ route('admin.unidades.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nueva unidad</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @forelse ($unidades as $unidad)
                    <div class="flex justify-between items-center p-4 gap-4">
                        <div>
                            <a href="{{ route('admin.unidades.show', $unidad) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $unidad->orden }}. {{ $unidad->titulo }}
                            </a>
                            <p class="text-sm text-gray-500">{{ $unidad->lecciones_count }} lecciones ·
                                <span class="{{ $unidad->publicada ? 'text-green-700' : 'text-yellow-700' }}">{{ $unidad->publicada ? 'Publicada' : 'Borrador' }}</span>
                            </p>
                        </div>
                        <div class="flex gap-4 text-sm">
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
                @empty
                    <p class="p-6 text-center text-gray-500">Todavía no hay unidades. Crea la primera con "Nueva unidad".</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
