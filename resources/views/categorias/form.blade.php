<x-app.layout>
    <x-app.page-header
        title="{{ $category ? 'Editar Categoria' : 'Nova Categoria' }}"
        subtitle="{{ $category ? $category->name : 'Cadastre uma nova categoria' }}"
        icon="tag"
    >
        <x-app.btn as="a" href="{{ route('categorias.index') }}" variant="ghost">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $category ? route('categorias.update', $category) : route('categorias.store') }}" class="max-w-md space-y-4">
            @csrf
            @if ($category)@method('PUT')@endif

            <x-app.input label="Nome *" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Nome da categoria" required />

            <div>
                <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Cor</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="{{ old('color', $category->color ?? '#c8102e') }}" class="h-10 w-10 cursor-pointer rounded border border-mv-border bg-mv-surface2">
                    <span class="text-[14px] text-mv-text-muted">Cor da categoria (opcional)</span>
                </div>
            </div>

            <div class="flex gap-2">
                <x-app.btn type="submit" icon="check">{{ $category ? 'Salvar alterações' : 'Cadastrar Categoria' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('categorias.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>
