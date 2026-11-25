<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ejercicio {{ $ejercicio->orden }} · {{ $ejercicio->etiqueta() }}</h2>
            <a href="{{ route('alumno.lecciones.show', $leccion) }}" class="text-sm text-gray-600 hover:underline">← {{ $leccion->titulo }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Resultado inmediato (RF017) --}}
            @if ($resultado)
                @if ($resultado['correcto'])
                    <div class="p-4 bg-green-100 text-green-800 rounded-md space-y-3">
                        <p class="font-semibold">¡Correcto!</p>
                        @if ($resultado['unidad_completada'])
                            <p>🎉 Completaste la unidad «{{ $resultado['unidad_completada'] }}». Si hay una siguiente unidad, ya quedó desbloqueada.</p>
                        @endif
                        <div class="flex gap-3">
                            @if ($siguiente)
                                <a href="{{ route('alumno.ejercicios.show', $siguiente) }}" class="inline-flex items-center px-4 py-2 bg-green-700 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-600">Siguiente ejercicio</a>
                            @endif
                            <a href="{{ route('alumno.panel') }}" class="inline-flex items-center px-4 py-2 bg-white text-green-800 border border-green-300 rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-50">Ir a mi ruta</a>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-red-100 text-red-800 rounded-md">
                        <p class="font-semibold">Respuesta incorrecta.</p>
                        <p>Revisa la lección e inténtalo de nuevo.</p>
                    </div>
                @endif
            @elseif ($yaResuelto)
                <div class="p-4 bg-blue-50 text-blue-800 rounded-md">Ya resolviste este ejercicio. Puedes repetirlo si quieres practicar.</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                <p class="text-gray-900 text-lg whitespace-pre-line">{{ $ejercicio->enunciado }}</p>

                <form method="POST" action="{{ route('alumno.ejercicios.responder', $ejercicio) }}" class="space-y-5">
                    @csrf

                    {{-- OPCIÓN MÚLTIPLE --}}
                    @if ($ejercicio->tipo === 'opcion_multiple')
                        @if ($ejercicio->codigo_base)
                            <pre class="bg-gray-900 text-gray-100 p-4 rounded-md overflow-x-auto text-sm"><code>{{ $ejercicio->codigo_base }}</code></pre>
                        @endif

                        <div class="space-y-2">
                            @foreach ($opciones as $opcion)
                                <label class="flex items-center gap-3 p-3 border rounded-md cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="opcion" value="{{ $opcion['id'] }}" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="font-mono text-sm whitespace-pre-wrap">{{ $opcion['texto'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error :messages="$errors->get('opcion')" />

                    {{-- COMPLETAR CÓDIGO --}}
                    @elseif ($ejercicio->tipo === 'completar')
                        <p class="text-sm text-gray-500">Escribe en el espacio resaltado la parte que falta.</p>
                        <pre class="bg-gray-900 text-gray-100 p-4 rounded-md overflow-x-auto text-sm leading-8">{{ $antes }}<input type="text" name="respuesta" value="{{ old('respuesta') }}" autocomplete="off" spellcheck="false" class="inline-block w-56 py-0 px-2 bg-gray-800 border-2 border-yellow-400 rounded text-yellow-200 font-mono text-sm focus:border-yellow-300 focus:ring-0">{{ $despues }}</pre>
                        <x-input-error :messages="$errors->get('respuesta')" />

                    {{-- ORDENAR LÍNEAS --}}
                    @else
                        <p class="text-sm text-gray-500">Arrastra las líneas (o usa las flechas) para ponerlas en el orden correcto.</p>

                        <div x-data="{
                                items: @js($lineas),
                                dragIndex: null,
                                mover(desde, hacia) {
                                    if (desde === null || hacia < 0 || hacia >= this.items.length) { return; }
                                    const [item] = this.items.splice(desde, 1);
                                    this.items.splice(hacia, 0, item);
                                },
                                get orden() { return this.items.map(i => i.id).join(','); }
                            }" class="space-y-2">

                            <template x-for="(item, index) in items" :key="item.id">
                                <div draggable="true"
                                     @dragstart="dragIndex = index"
                                     @dragover.prevent
                                     @drop.prevent="mover(dragIndex, index); dragIndex = null"
                                     class="flex items-center gap-3 bg-gray-900 text-gray-100 rounded-md px-3 py-2 cursor-move">
                                    <span class="text-gray-500 select-none">⠿</span>
                                    <span class="font-mono text-sm whitespace-pre grow" x-text="item.texto"></span>
                                    <button type="button" @click="mover(index, index - 1)" class="px-2 text-gray-300 hover:text-white" title="Subir">↑</button>
                                    <button type="button" @click="mover(index, index + 1)" class="px-2 text-gray-300 hover:text-white" title="Bajar">↓</button>
                                </div>
                            </template>

                            <input type="hidden" name="orden" :value="orden">
                        </div>
                        <x-input-error :messages="$errors->get('orden')" />
                    @endif

                    <x-primary-button>Enviar respuesta</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
