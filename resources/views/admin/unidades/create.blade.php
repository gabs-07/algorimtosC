<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva unidad</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.unidades.store') }}" class="space-y-6">
                    @csrf
                    @include('admin.unidades._form')
                    <div class="flex items-center gap-4">
                        <x-primary-button>Crear unidad</x-primary-button>
                        <a href="{{ route('admin.unidades.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
