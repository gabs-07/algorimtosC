<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $leccion->titulo }}</h2>
            <a href="{{ route('alumno.unidades.show', $unidad) }}" class="text-sm text-gray-600 hover:underline">← {{ $unidad->titulo }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 sm:p-8">
                <x-markdown :texto="$leccion->contenido" />
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <h3 class="font-semibold text-gray-800 p-4 border-b">Ejercicios de esta lección</h3>
                <div class="divide-y">
                    @forelse ($ejercicios as $ejercicio)
                        <a href="{{ route('alumno.ejercicios.show', $ejercicio) }}" class="flex justify-between items-center p-4 hover:bg-gray-50">
                            <div>
                                <p class="text-gray-900">{{ $ejercicio->orden }}. {{ \Illuminate\Support\Str::limit($ejercicio->enunciado, 80) }}</p>
                                <p class="text-sm text-gray-500">{{ $ejercicio->etiqueta() }}</p>
                            </div>
                            @if ($resueltos->has($ejercicio->id))
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Resuelto</span>
                            @else
                                <span class="text-indigo-600 text-sm">Resolver →</span>
                            @endif
                        </a>
                    @empty
                        <p class="p-6 text-center text-gray-500">Esta lección todavía no tiene ejercicios.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
