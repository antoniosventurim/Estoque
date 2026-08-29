<x-app.layout>
    <x-app.page-header
        title="{{ $unit ? 'Editar Unidade' : 'Nova Unidade' }}"
        subtitle="{{ $unit ? $unit->name : 'Cadastre uma nova unidade de medida' }}"
        icon="ruler"
    >
        <x-app.btn as="a" href="{{ route('unidades.index') }}" variant="ghost">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $unit ? route('unidades.update', $unit) : route('unidades.store') }}" class="max-w-md space-y-4">
            @csrf
            @if ($unit)@method('PUT')@endif

            <x-app.input label="Nome *" name="name" value="{{ old('name', $unit->name ?? '') }}" placeholder="Ex: Quilograma" required />
            <x-app.input label="Abreviação *" name="abbreviation" value="{{ old('abbreviation', $unit->abbreviation ?? '') }}" placeholder="Ex: kg" mono maxlength="10" required />

            <div class="flex gap-2">
                <x-app.btn type="submit" icon="check">{{ $unit ? 'Salvar alterações' : 'Cadastrar Unidade' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('unidades.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>
