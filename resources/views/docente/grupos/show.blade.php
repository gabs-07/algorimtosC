<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grupo {{ $grupo->nombre }}</h2>
            <a href="{{ route('docente.grupos.index') }}" class="text-sm text-gray-600 hover:underline">← Mis grupos</a>
        </div>
    </x-slot>

    @php
        $sumaTotal = 0;
        $sumaResueltos = 0;
        foreach ($matriz as $fila) {
            $sumaTotal += $fila->sum('total');
            $sumaResueltos += $fila->sum('resueltos');
        }
        $promedioGrupo = $sumaTotal > 0 ? (int) round($sumaResueltos * 100 / $sumaTotal) : 0;
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            {{-- Agregar alumno --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Agregar alumno</h3>
                <form method="POST" action="{{ route('docente.grupos.alumnos.agregar', $grupo) }}" class="flex flex-wrap gap-3 items-start">
                    @csrf
                    <div class="grow">
                        <x-text-input name="email" type="email" class="block w-full" placeholder="Correo del alumno" :value="old('email')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                    <x-primary-button>Agregar</x-primary-button>
                </form>
            </div>

            {{-- Alumnos --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-3">Alumnos ({{ $alumnos->count() }})</h3>
                @forelse ($alumnos as $alumno)
                    <div class="flex justify-between items-center border-b py-2">
                        <div>
                            <p>{{ $alumno->nombre }}</p>
                            <p class="text-sm text-gray-500">{{ $alumno->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('docente.grupos.alumnos.quitar', [$grupo, $alumno]) }}" onsubmit="return confirm('¿Quitar a {{ $alumno->nombre }} del grupo?')">
                            @csrf @method('DELETE')
                            <button class="text-sm text-red-600 hover:underline">Quitar</button>
                        </form>
                    </div>
                @empty
                    <p class="text-gray-500">Este grupo todavía no tiene alumnos.</p>
                @endforelse
            </div>

            {{-- Avance del grupo --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap justify-between items-center gap-2 mb-4">
                    <h3 class="font-semibold text-gray-800">Avance del grupo</h3>
                    <span class="text-sm text-gray-600">Promedio del grupo: <strong>{{ $promedioGrupo }}%</strong></span>
                </div>

                @if ($alumnos->isEmpty() || $unidades->isEmpty())
                    <p class="text-gray-500">Aún no hay alumnos o unidades publicadas para mostrar el avance.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-gray-600 border-b">
                                <tr>
                                    <th class="py-2 pe-4">Alumno</th>
                                    @foreach ($unidades as $unidad)
                                        <th class="py-2 px-3">{{ $unidad->orden }}. {{ $unidad->titulo }}</th>
                                    @endforeach
                                    <th class="py-2 ps-3">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($alumnos as $alumno)
                                    @php
                                        $fila = $matriz[$alumno->id];
                                        $totalAlumno = $fila->sum('total');
                                        $resueltosAlumno = $fila->sum('resueltos');
                                    @endphp
                                    <tr>
                                        <td class="py-2 pe-4 whitespace-nowrap">{{ $alumno->nombre }}</td>
                                        @foreach ($unidades as $unidad)
                                            @php $celda = $fila->get($unidad->id); @endphp
                                            <td class="py-2 px-3 whitespace-nowrap">
                                                @if (! $celda)
                                                    <span class="text-gray-400">—</span>
                                                @elseif ($celda['estado'] === 'bloqueada')
                                                    <span class="text-gray-400">Bloqueada</span>
                                                @elseif ($celda['estado'] === 'completada')
                                                    <span class="text-green-700 font-semibold">{{ $celda['porcentaje'] }}% ✓</span>
                                                @else
                                                    <span class="text-indigo-700">{{ $celda['porcentaje'] }}%</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="py-2 ps-3 font-semibold">{{ $totalAlumno > 0 ? (int) round($resueltosAlumno * 100 / $totalAlumno) : 0 }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Ejercicios con más errores --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Ejercicios con más errores</h3>

                @forelse ($errores as $fila)
                    @php $e = $fila['ejercicio']; @endphp
                    <div class="flex justify-between items-center gap-4 border-b py-2">
                        <div>
                            <p class="text-gray-900">{{ \Illuminate\Support\Str::limit($e->enunciado, 80) }}</p>
                            <p class="text-xs text-gray-500">{{ $e->leccion->unidad->titulo }} › {{ $e->leccion->titulo }} · {{ $e->etiqueta() }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-red-700">{{ $fila['errores'] }} errores</p>
                            <p class="text-xs text-gray-500">de {{ $fila['total'] }} intentos ({{ $fila['porcentaje'] }}%)</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">Todavía no hay errores registrados en este grupo.</p>
                @endforelse
            </div>

            {{-- Desbloquear unidad (RF011) --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="font-semibold text-gray-800 mb-1">Desbloquear unidad</h3>
                <p class="text-sm text-gray-500 mb-4">La unidad quedará disponible de inmediato, aunque el alumno no haya completado la anterior.</p>

                @if ($alumnos->isEmpty())
                    <p class="text-gray-500">Agrega alumnos al grupo para poder desbloquear unidades.</p>
                @else
                    <form method="POST" action="{{ route('docente.grupos.desbloqueos', $grupo) }}" class="space-y-4" x-data="{ todos: false }">
                        @csrf

                        <div>
                            <x-input-label for="unidad_id" value="Unidad" />
                            <select id="unidad_id" name="unidad_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Elige una unidad…</option>
                                @foreach ($unidades as $unidad)
                                    <option value="{{ $unidad->id }}" @selected((int) old('unidad_id') === $unidad->id)>{{ $unidad->orden }}. {{ $unidad->titulo }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('unidad_id')" />
                        </div>

                        <div>
                            <label class="inline-flex items-center gap-2 font-medium text-gray-800">
                                <input type="checkbox" name="todos" value="1" x-model="todos" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                Todo el grupo
                            </label>

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-1" :class="todos ? 'opacity-40' : ''">
                                @foreach ($alumnos as $alumno)
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                        <input type="checkbox" name="alumnos[]" value="{{ $alumno->id }}" :disabled="todos" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        {{ $alumno->nombre }}
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('alumnos')" />
                        </div>

                        <x-primary-button>Desbloquear unidad</x-primary-button>
                    </form>
                @endif

                @if ($desbloqueos->isNotEmpty())
                    <h4 class="font-semibold text-gray-800 mt-6 mb-2 text-sm">Desbloqueos recientes</h4>
                    <div class="text-sm divide-y">
                        @foreach ($desbloqueos as $d)
                            <div class="flex justify-between py-1.5">
                                <span>{{ $d->alumno->nombre }} · {{ $d->unidad->titulo }}</span>
                                <span class="text-gray-500">{{ $d->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="text-sm">
                <a href="{{ route('docente.grupos.edit', $grupo) }}" class="text-indigo-600 hover:underline">Cambiar nombre del grupo</a>
            </div>
        </div>
    </div>
</x-app-layout>
