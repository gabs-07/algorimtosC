<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mi ruta de aprendizaje</h2>
    </x-slot>

    @php
        $totalEj = $items->sum('total');
        $totalRes = $items->sum('resueltos');
        $pctGeneral = $totalEj > 0 ? (int) round($totalRes * 100 / $totalEj) : 0;
    @endphp

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-900">Hola, {{ auth()->user()->nombre }}. Avanza a tu propio ritmo: completa todos los ejercicios de una unidad para desbloquear la siguiente.</p>
                <p class="mt-2 text-sm"><a href="{{ route('profile.edit') }}#logros" class="text-indigo-600 hover:underline">Ver mis logros →</a></p>
                <div class="mt-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Avance general</span>
                        <span>{{ $totalRes }} de {{ $totalEj }} ejercicios · {{ $pctGeneral }}%</span>
                    </div>
                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-3 bg-indigo-600" style="width: {{ $pctGeneral }}%"></div>
                    </div>
                </div>
            </div>

            @forelse ($items as $item)
                @php
                    $u = $item['unidad'];
                    $estado = $item['estado'];
                    $bloqueada = $estado === 'bloqueada';
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-6 {{ $bloqueada ? 'opacity-60' : '' }}">
                    <div class="flex flex-wrap justify-between items-start gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $u->orden }}. {{ $u->titulo }}</h3>
                            @if ($u->descripcion)
                                <p class="text-sm text-gray-600 mt-1">{{ $u->descripcion }}</p>
                            @endif
                        </div>

                        @if ($estado === 'completada')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completada</span>
                        @elseif ($estado === 'disponible')
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Disponible</span>
                        @else
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-700">Bloqueada</span>
                        @endif
                    </div>

                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ $item['resueltos'] }} de {{ $item['total'] }} ejercicios</span>
                            <span>{{ $item['porcentaje'] }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-2 {{ $estado === 'completada' ? 'bg-green-500' : 'bg-indigo-600' }}" style="width: {{ $item['porcentaje'] }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if ($bloqueada)
                            <p class="text-sm text-gray-500">Completa la unidad anterior para desbloquearla.</p>
                        @else
                            <a href="{{ route('alumno.unidades.show', $u) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">
                                {{ $estado === 'completada' ? 'Repasar' : ($item['resueltos'] > 0 ? 'Continuar' : 'Empezar') }}
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    Aún no hay unidades disponibles.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
