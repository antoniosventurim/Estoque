<x-app.layout>
    <x-app.page-header
        title="Funcionários"
        subtitle="{{ $employees->total() }} funcionário(s) cadastrado(s)"
        icon="user"
    >
        <x-app.btn as="a" href="{{ route('funcionarios.create') }}" icon="plus">Novo Funcionário</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap items-center gap-2.5">
        <form method="GET" action="{{ route('funcionarios.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
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
                        @foreach (['#', 'Nome', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $i => $e)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $employees->firstItem() + $i }}</td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">{{ $e->name }}</td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('funcionarios.edit', $e) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('funcionarios.destroy', $e) }}" onsubmit="return confirm('Excluir este funcionário?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhum funcionário encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-app.pagination :paginator="$employees" />
    </x-app.card>
</x-app.layout>
