<x-app.layout>
    <x-app.page-header
        title="{{ $employee ? 'Editar Funcionário' : 'Novo Funcionário' }}"
        subtitle="{{ $employee ? $employee->name : 'Cadastre um funcionário para vínculo nas retiradas' }}"
        icon="user"
    >
        <x-app.btn as="a" href="{{ route('funcionarios.index') }}" variant="ghost">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $employee ? route('funcionarios.update', $employee) : route('funcionarios.store') }}" class="max-w-md space-y-4">
            @csrf
            @if ($employee)@method('PUT')@endif

            <x-app.input label="Nome *" name="name" value="{{ old('name', $employee->name ?? '') }}" placeholder="Nome do funcionário" required autofocus />

            <div class="flex gap-2">
                <x-app.btn type="submit" icon="check">{{ $employee ? 'Salvar alterações' : 'Cadastrar Funcionário' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('funcionarios.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>
