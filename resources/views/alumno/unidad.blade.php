<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $unidad->orden }}. {{ $unidad->titulo }}</h2>
            <a href="{{ route('alumno.panel') }}" class="text-sm text-gray-600 hover:underline">← Mi ruta</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if ($unidad->descripcion)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-700">{{ $unidad->descripcion }}</div>
            @endif

            @forelse ($lecciones as $leccion)
                @php
                    $total = $leccion->ejercicios->count();
                    $hechos = $leccion->ejercicios->filter(fn ($e) => $resueltos->has($e->id))->count();
                @endphp
                <a href="{{ route('alumno.lecciones.show', $leccion) }}" class="block bg-white shadow-sm sm:rounded-lg p-6 hover:bg-gray-50">
                    <div class="flex justify-between items-center gap-4">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $leccion->orden }}. {{ $leccion->titulo }}</p>
                            <p class="text-sm text-gray-500">{{ $hechos }} de {{ $total }} ejercicios resueltos</p>
                        </div>
                        @if ($total > 0 && $hechos === $total)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completada</span>
                        @else
                            <span class="text-indigo-600 text-sm">Abrir →</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-500">Esta unidad todavía no tiene lecciones.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
