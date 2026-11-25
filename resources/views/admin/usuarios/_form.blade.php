@php($editando = $usuario->exists)

<div>
    <x-input-label for="nombre" value="Nombre completo" />
    <x-text-input id="nombre" name="nombre" type="text" class="mt-1 block w-full" :value="old('nombre', $usuario->nombre)" required autofocus />
    <x-input-error class="mt-2" :messages="$errors->get('nombre')" />
</div>

<div>
    <x-input-label for="email" value="Correo electrónico" />
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email)" required />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div>
    <x-input-label for="password" :value="$editando ? 'Nueva contraseña (déjala vacía para no cambiarla)' : 'Contraseña'" />
    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $editando" />
    <x-input-error class="mt-2" :messages="$errors->get('password')" />
</div>

<div>
    <x-input-label for="rol" value="Rol" />
    <select id="rol" name="rol" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @foreach (['admin' => 'Administrador', 'docente' => 'Docente', 'alumno' => 'Alumno'] as $valor => $etiqueta)
            <option value="{{ $valor }}" @selected(old('rol', $usuario->rol) === $valor)>{{ $etiqueta }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('rol')" />
</div>

<div class="flex items-center">
    <input type="hidden" name="activo" value="0">
    <input id="activo" name="activo" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('activo', $usuario->activo))>
    <label for="activo" class="ms-2 text-sm text-gray-700">Cuenta activa</label>
</div>
