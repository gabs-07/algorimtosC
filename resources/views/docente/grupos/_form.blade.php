<div>
    <x-input-label for="nombre" value="Nombre del grupo (por ejemplo, 6CV3)" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $grupo->nombre)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
</div>
