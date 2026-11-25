<div>
    <x-input-label for="titulo" value="Título de la unidad" />
    <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo', $unidad->titulo)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('titulo')" />
</div>

<div>
    <x-input-label for="descripcion" value="Descripción breve" />
    <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('descripcion', $unidad->descripcion) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('descripcion')" />
</div>

<div>
    <x-input-label for="orden" value="Orden en la ruta (1 = la más fácil, se ve primero)" />
    <x-text-input id="orden" name="orden" type="number" min="1" class="mt-1 block w-32" :value="old('orden', $unidad->orden)" required />
    <x-input-error class="mt-2" :messages="$errors->get('orden')" />
</div>

<div class="flex items-center">
    <input type="hidden" name="publicada" value="0">
    <input id="publicada" name="publicada" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('publicada', $unidad->publicada))>
    <label for="publicada" class="ms-2 text-sm text-gray-700">Publicada (visible para los alumnos)</label>
</div>
