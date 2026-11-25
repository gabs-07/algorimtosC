<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $leccion->titulo }}</h2>
            <a href="{{ route('admin.unidades.show', $leccion->unidad) }}" class="text-sm text-gray-600 hover:underline">← {{ $leccion->unidad->titulo }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="font-semibold text-gray-800">Contenido (así lo verá el alumno)</h3>
                    <a href="{{ route('admin.lecciones.edit', $leccion) }}" class="text-sm text-indigo-600 hover:underline">Editar lección</a>
                </div>
                <x-markdown :texto="$leccion->contenido" />
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="font-semibold text-gray-800">Ejercicios</h3>
                    <a href="{{ route('admin.lecciones.ejercicios.create', $leccion) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nuevo ejercicio</a>
                </div>
                <div class="divide-y">
                    @forelse ($ejercicios as $ejercicio)
                        <div class="flex justify-between items-center p-4 gap-4">
                            <div>
                                <p class="font-medium text-gray-900">{{ $ejercicio->orden }}. {{ \Illuminate\Support\Str::limit($ejercicio->enunciado, 90) }}</p>
                                <p class="text-sm text-gray-500">{{ $ejercicio->etiqueta() }} ·
                                    <span class="{{ $ejercicio->publicado ? 'text-green-700' : 'text-yellow-700' }}">{{ $ejercicio->publicado ? 'Publicado' : 'Borrador' }}</span>
                                </p>
                            </div>
                            <div class="flex gap-4 text-sm shrink-0">
                                <form method="POST" action="{{ route('admin.ejercicios.publicar', $ejercicio) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-yellow-700 hover:underline">{{ $ejercicio->publicado ? 'Ocultar' : 'Publicar' }}</button>
                                </form>
                                <a href="{{ route('admin.ejercicios.edit', $ejercicio) }}" class="text-indigo-600 hover:underline">Editar</a>
                                <form method="POST" action="{{ route('admin.ejercicios.destroy', $ejercicio) }}" onsubmit="return confirm('¿Eliminar este ejercicio?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="p-6 text-center text-gray-500">Esta lección todavía no tiene ejercicios.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
