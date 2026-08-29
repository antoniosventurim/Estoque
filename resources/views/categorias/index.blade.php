<x-app.layout>
    <x-app.page-header
        title="Categorias"
        subtitle="{{ $categories->total() }} categoria(s) cadastrada(s)"
        icon="tag"
    >
        <x-app.btn as="a" href="{{ route('categorias.create') }}" icon="plus">Nova Categoria</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        <form method="GET" action="{{ route('categorias.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
            <div class="relative min-w-[240px] flex-1">
                <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nome..."
                    class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none">
            </div>
            <x-app.btn type="submit" icon="search" variant="secondary">Filtrar</x-app.btn>
        </form>
    </div>

    {{-- Tabela --}}
    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Nome', 'Cor', 'Produtos', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $i => $c)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $categories->firstItem() + $i }}</td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">{{ $c->name }}</td>
                            <td class="px-3.5 py-2.5">
                                @if ($c->color)
                                    <span class="inline-block h-4 w-4 rounded" style="background: {{ $c->color }}"></span>
                                @else
                                    <span class="text-mv-text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $c->products_count }}</td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('categorias.edit', $c) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('categorias.destroy', $c) }}" onsubmit="return confirm('Excluir esta categoria?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhuma categoria encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$categories" />
    </x-app.card>
</x-app.layout>
