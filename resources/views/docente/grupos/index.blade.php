<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis grupos</h2>
            <a href="{{ route('docente.grupos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700">Nuevo grupo</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg divide-y">
                @forelse ($grupos as $grupo)
                    <div class="flex justify-between items-center p-4">
                        <div>
                            <a href="{{ route('docente.grupos.show', $grupo) }}" class="font-medium text-indigo-600 hover:underline">{{ $grupo->nombre }}</a>
                            <p class="text-sm text-gray-500">{{ $grupo->alumnos_count }} alumnos</p>
                        </div>
                        <div class="flex gap-4 text-sm">
                            <a href="{{ route('docente.grupos.edit', $grupo) }}" class="text-indigo-600 hover:underline">Editar</a>
                            <form method="POST" action="{{ route('docente.grupos.destroy', $grupo) }}" onsubmit="return confirm('¿Eliminar el grupo {{ $grupo->nombre }}? Los alumnos no se borran, solo dejan de pertenecer al grupo.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Eliminar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-gray-500">Todavía no tienes grupos. Crea el primero con el botón "Nuevo grupo".</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
