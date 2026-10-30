<x-app.layout>
    <x-app.page-header
        title="{{ $category ? 'Editar Categoria' : 'Nova Categoria' }}"
        subtitle="{{ $category ? $category->name : 'Cadastre uma nova categoria' }}"
        icon="tag"
    >
        <x-app.btn as="a" href="{{ route('categorias.index') }}" variant="back">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $category ? route('categorias.update', $category) : route('categorias.store') }}" class="max-w-md space-y-4">
            @csrf
            @if ($category)@method('PUT')@endif

            <x-app.input label="Nome *" name="name" value="{{ old('name', $category->name ?? '') }}" placeholder="Nome da categoria" required />

            <div>
                <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Validade</label>
                <label class="cursor-pointer select-none">
                    <input type="checkbox" name="requires_expiration" value="1" class="peer sr-only" {{ (bool) old('requires_expiration', $category->requires_expiration ?? false) ? 'checked' : '' }}>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-mv-border bg-mv-surface2 px-3 py-1.5 text-[13px] font-medium text-mv-text-secondary transition-colors peer-checked:border-mv-accent peer-checked:bg-mv-accent peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-mv-accent">
                        <x-app.icon name="check" :size="12" />
                        Exige data de validade na entrada
                    </span>
                </label>
                <p class="mt-1.5 text-xs text-mv-text-muted">Marque para categorias como Alimentos, onde o produto precisa de validade a cada entrada.</p>
            </div>

            <div class="flex gap-2">
                <x-app.btn type="submit" icon="check">{{ $category ? 'Salvar alterações' : 'Cadastrar Categoria' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('categorias.index') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>
