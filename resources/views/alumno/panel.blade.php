<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mi ruta de aprendizaje</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-900">Hola, {{ auth()->user()->nombre }}. Aquí verás tus unidades, tus ejercicios y tu avance.</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <p class="text-gray-500">Aún no hay unidades disponibles.</p>
            </div>
        </div>
    </div>
</x-app-layout>
