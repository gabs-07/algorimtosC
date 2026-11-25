<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Usuarios</h2>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nuevo usuario</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="p-4 bg-red-100 text-red-800 rounded-md">{{ session('error') }}</div>
            @endif

            <form method="GET" class="bg-white shadow-sm sm:rounded-lg p-4 flex flex-wrap gap-3 items-end">
                <div class="grow">
                    <x-input-label for="q" value="Buscar" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" placeholder="Nombre o correo" :value="request('q')" />
                </div>
                <div>
                    <x-input-label for="rol" value="Rol" />
                    <select id="rol" name="rol" class="mt-1 border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        @foreach (['admin' => 'Administrador', 'docente' => 'Docente', 'alumno' => 'Alumno'] as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(request('rol') === $valor)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado" class="mt-1 border-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                        <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
                    </select>
                </div>
                <x-primary-button>Filtrar</x-primary-button>
                <a href="{{ route('admin.usuarios.index') }}" class="text-sm text-gray-600 hover:underline pb-2">Limpiar</a>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-4 py-3">Nombre</th>
                            <th class="px-4 py-3">Correo</th>
                            <th class="px-4 py-3">Rol</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Correo verificado</th>
                            <th class="px-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($usuarios as $u)
                            <tr>
                                <td class="px-4 py-3">{{ $u->nombre }}</td>
                                <td class="px-4 py-3">{{ $u->email }}</td>
                                <td class="px-4 py-3 capitalize">{{ $u->rol }}</td>
                                <td class="px-4 py-3">
                                    <span class="{{ $u->activo ? 'text-green-700' : 'text-red-700' }}">{{ $u->activo ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td class="px-4 py-3">{{ $u->email_verified_at ? 'Sí' : 'No' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('admin.usuarios.edit', $u) }}" class="text-indigo-600 hover:underline">Editar</a>

                                        @unless ($u->is(auth()->user()))
                                            <form method="POST" action="{{ route('admin.usuarios.estado', $u) }}">
                                                @csrf @method('PATCH')
                                                <button class="text-yellow-700 hover:underline">{{ $u->activo ? 'Desactivar' : 'Activar' }}</button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}" onsubmit="return confirm('¿Eliminar a {{ $u->nombre }}? Esta acción no se puede deshacer.')">
                                                @csrf @method('DELETE')
                                                <button class="text-red-600 hover:underline">Eliminar</button>
                                            </form>
                                        @endunless

                                        @unless ($u->email_verified_at)
                                            <form method="POST" action="{{ route('admin.usuarios.verificar', $u) }}">
                                                @csrf @method('PATCH')
                                                <button class="text-green-700 hover:underline">Verificar</button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No se encontraron usuarios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usuarios->links() }}
        </div>
    </div>
</x-app-layout>
