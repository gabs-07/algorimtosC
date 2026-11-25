<div x-data="{ tipo: '{{ old('tipo', $ejercicio->tipo) }}' }" class="space-y-6">

    <div>
        <x-input-label for="tipo" value="Tipo de ejercicio" />
        @if ($ejercicio->exists)
            <p class="mt-1 font-medium text-gray-800">{{ $ejercicio->etiqueta() }} <span class="text-xs text-gray-500">(el tipo no se puede cambiar)</span></p>
            <input type="hidden" name="tipo" value="{{ $ejercicio->tipo }}">
        @else
            <select id="tipo" name="tipo" x-model="tipo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (\App\Models\Ejercicio::ETIQUETAS as $valor => $etiqueta)
                    <option value="{{ $valor }}">{{ $etiqueta }}</option>
                @endforeach
            </select>
        @endif
        <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
    </div>

    <div>
        <x-input-label for="enunciado" value="Enunciado (lo que se le pide al alumno)" />
        <textarea id="enunciado" name="enunciado" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('enunciado', $ejercicio->enunciado) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('enunciado')" />
    </div>

    <div x-show="tipo !== 'ordenar'">
        <x-input-label for="codigo_base">
            <span x-show="tipo === 'opcion_multiple'">Fragmento de código (opcional)</span>
            <span x-show="tipo === 'completar'" x-cloak>Código con el espacio a completar: marca con ___ (tres guiones bajos)</span>
        </x-input-label>
        <textarea id="codigo_base" name="codigo_base" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('codigo_base', $ejercicio->codigo_base) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('codigo_base')" />
    </div>

    {{-- Opción múltiple --}}
    <div x-show="tipo === 'opcion_multiple'" class="space-y-2">
        <x-input-label value="Opciones (mínimo 2, máximo 4). Marca la correcta." />
        @for ($i = 0; $i < 4; $i++)
            <div class="flex items-center gap-3">
                <input type="radio" name="correcta" value="{{ $i }}" class="text-indigo-600 focus:ring-indigo-500" @checked((int) old('correcta', $datos['correcta']) === $i)>
                <x-text-input name="opciones[{{ $i }}]" type="text" class="block w-full font-mono text-sm" placeholder="Opción {{ $i + 1 }}" :value="old('opciones.'.$i, $datos['opciones'][$i])" />
            </div>
        @endfor
        <x-input-error class="mt-2" :messages="$errors->get('opciones')" />
        <x-input-error class="mt-2" :messages="$errors->get('correcta')" />
        <p class="text-xs text-gray-500">El orden de las opciones se baraja para cada alumno en cada intento.</p>
    </div>

    {{-- Completar --}}
    <div x-show="tipo === 'completar'" x-cloak>
        <x-input-label for="soluciones" value="Respuestas válidas (una por línea)" />
        <textarea id="soluciones" name="soluciones" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('soluciones', $datos['soluciones']) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Se comparan ignorando diferencias de espacios. Escribe aquí todas las variantes aceptadas.</p>
        <x-input-error class="mt-2" :messages="$errors->get('soluciones')" />
    </div>

    {{-- Ordenar --}}
    <div x-show="tipo === 'ordenar'" x-cloak>
        <x-input-label for="lineas" value="Líneas de código en el ORDEN CORRECTO (una por línea)" />
        <textarea id="lineas" name="lineas" rows="8" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('lineas', $datos['lineas']) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">El alumno las verá desordenadas y tendrá que reordenarlas.</p>
        <x-input-error class="mt-2" :messages="$errors->get('lineas')" />
    </div>

    <div>
        <x-input-label for="orden" value="Orden dentro de la lección" />
        <x-text-input id="orden" name="orden" type="number" min="1" class="mt-1 block w-32" :value="old('orden', $ejercicio->orden)" required />
        <x-input-error class="mt-2" :messages="$errors->get('orden')" />
    </div>

    <div class="flex items-center">
        <input type="hidden" name="publicado" value="0">
        <input id="publicado" name="publicado" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('publicado', $ejercicio->publicado))>
        <label for="publicado" class="ms-2 text-sm text-gray-700">Publicado (visible para los alumnos)</label>
    </div>
</div>
