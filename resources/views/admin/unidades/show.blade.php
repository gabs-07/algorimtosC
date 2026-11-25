<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Unidad {{ $unidad->orden }}: {{ $unidad->titulo }}</h2>
            <a href="{{ route('admin.unidades.index') }}" class="text-sm text-gray-600 hover:underline">← Unidades</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex justify-between gap-4">
                <div>
                    <p class="text-gray-700">{{ $unidad->descripcion ?: 'Sin descripción.' }}</p>
                    <p class="text-sm mt-2 {{ $unidad->publicada ? 'text-green-700' : 'text-yellow-700' }}">{{ $unidad->publicada ? 'Publicada' : 'Borrador (los alumnos no la ven)' }}</p>
                </div>
                <a href="{{ route('admin.unidades.edit', $unidad) }}" class="text-sm text-indigo-600 hover:underline shrink-0">Editar unidad</a>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="font-semibold text-gray-800">Lecciones</h3>
                    <a href="{{ route('admin.unidades.lecciones.create', $unidad) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nueva lección</a>
                </div>
                <div class="divide-y">
                    @forelse ($lecciones as $leccion)
                        <div class="flex justify-between items-center p-4">
                            <div>
                                <a href="{{ route('admin.lecciones.show', $leccion) }}" class="font-medium text-indigo-600 hover:underline">{{ $leccion->orden }}. {{ $leccion->titulo }}</a>
                                <p class="text-sm text-gray-500">{{ $leccion->ejercicios_count }} ejercicios</p>
                            </div>
                            <div class="flex gap-4 text-sm">
                                <a href="{{ route('admin.lecciones.edit', $leccion) }}" class="text-indigo-600 hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.lecciones.destroy', $leccion) }}" onsubmit="return confirm('¿Eliminar la lección y sus ejercicios?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="p-6 text-center text-gray-500">Esta unidad todavía no tiene lecciones.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
