<x-app.layout>
    <x-app.page-header
        title="Centros de Custo"
        subtitle="{{ $costCenters->total() }} centro(s) de custo cadastrado(s)"
        icon="building"
    >
        <x-app.btn as="a" href="{{ route('centros-custo.create') }}" icon="plus">Novo Centro de Custo</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        <form method="GET" action="{{ route('centros-custo.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
            <div class="relative min-w-[240px] flex-1">
                <div class="absolute left-2.5 top-1/2 -translate-y-1/2 text-mv-text-muted"><x-app.icon name="search" :size="14" /></div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Buscar por nome ou código..."
                    class="w-full rounded-md border border-mv-border bg-mv-surface py-2 pl-8 pr-2.5 text-[14px] text-mv-text outline-none">
            </div>
            <div class="relative w-[180px]">
                <x-app.searchable-select
                    name="type"
                    placeholder="Tipo"
                    :selected="$type"
                    :selected-label="$type !== '' ? (['curso' => 'Curso', 'setor' => 'Setor', 'laboratorio' => 'Laboratório', 'administrativo' => 'Administrativo'][$type] ?? '') : ''"
                    :options="collect([['id' => 'curso', 'name' => 'Curso', 'sub' => ''], ['id' => 'setor', 'name' => 'Setor', 'sub' => ''], ['id' => 'laboratorio', 'name' => 'Laboratório', 'sub' => ''], ['id' => 'administrativo', 'name' => 'Administrativo', 'sub' => '']])->values()->all()"
                />
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
                        @foreach (['#', 'Nome', 'Tipo', 'Código', 'Movimentações', 'Status', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($costCenters as $i => $c)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $costCenters->firstItem() + $i }}</td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">
                                <span class="inline-block h-2 w-2 rounded-full align-middle" style="background: {{ $c->color ?? '#c8102e' }}"></span>
                                <span class="ml-1.5">{{ $c->name }}</span>
                            </td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ ucfirst($c->type) }}</td>
                            <td class="mono px-3.5 py-2.5 text-xs text-mv-text-secondary">{{ $c->code }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $c->movements_count }}</td>
                            <td class="px-3.5 py-2.5">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[13px] font-medium {{ $c->is_active ? 'bg-mv-success-solid/15 text-mv-success' : 'bg-mv-danger-solid/15 text-mv-danger' }}">
                                    {{ $c->is_active ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('centros-custo.edit', $c) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('centros-custo.destroy', $c) }}" onsubmit="return confirm('Excluir este centro de custo?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhum centro de custo encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$costCenters" />
    </x-app.card>
</x-app.layout>