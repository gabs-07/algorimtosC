@php
    $catalogoLogros = \App\Models\Logro::orderBy('id')->get();
    $logrosObtenidos = auth()->user()->logros->keyBy('id');
@endphp

<section id="logros">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Mis logros</h2>
        <p class="mt-1 text-sm text-gray-600">
            Has obtenido {{ $logrosObtenidos->count() }} de {{ $catalogoLogros->count() }} logros.
        </p>
    </header>

    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse ($catalogoLogros as $logro)
            @php $obtenido = $logrosObtenidos->get($logro->id); @endphp
            <div class="flex items-start gap-3 p-3 rounded-md border {{ $obtenido ? 'bg-yellow-50 border-yellow-300' : 'bg-gray-50 border-gray-200 opacity-70' }}">
                <span class="text-2xl">{{ $obtenido ? '🏆' : '🔒' }}</span>
                <div>
                    <p class="font-semibold text-gray-900">{{ $logro->nombre }}</p>
                    <p class="text-sm text-gray-600">{{ $logro->descripcion }}</p>
                    <p class="text-xs mt-1 {{ $obtenido ? 'text-yellow-700' : 'text-gray-500' }}">
                        {{ $obtenido ? 'Obtenido el '.\Carbon\Carbon::parse($obtenido->pivot->obtenido_at)->format('d/m/Y') : 'Pendiente' }}
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Todavía no hay logros en el catálogo.</p>
        @endforelse
    </div>
</section>
