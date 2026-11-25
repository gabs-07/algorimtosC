<div>
    <x-input-label for="titulo" value="Título de la lección" />
    <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo', $leccion->titulo)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('titulo')" />
</div>

<div>
    <x-input-label for="contenido" value="Contenido (formato Markdown)" />
    <textarea id="contenido" name="contenido" rows="14" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm font-mono text-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('contenido', $leccion->contenido) }}</textarea>
    <p class="mt-1 text-xs text-gray-500">
        Usa <code>## Título</code> para encabezados, <code>- elemento</code> para listas y bloques de código con tres comillas invertidas seguidas de <code>c</code>.
    </p>
    <x-input-error class="mt-2" :messages="$errors->get('contenido')" />
</div>

<div>
    <x-input-label for="orden" value="Orden dentro de la unidad" />
    <x-text-input id="orden" name="orden" type="number" min="1" class="mt-1 block w-32" :value="old('orden', $leccion->orden)" required />
    <x-input-error class="mt-2" :messages="$errors->get('orden')" />
</div>
