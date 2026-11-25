<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grupo {{ $grupo->nombre }}</h2>
            <a href="{{ route('docente.grupos.index') }}" class="text-sm text-gray-600 hover:underline">← Mis grupos</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

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

            <div class="text-sm">
                <a href="{{ route('docente.grupos.edit', $grupo) }}" class="text-indigo-600 hover:underline">Cambiar nombre del grupo</a>
            </div>
        </div>
    </div>
</x-app-layout>
