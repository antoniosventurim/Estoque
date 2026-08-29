<x-app.layout>
    <x-app.page-header
        title="Unidades de Medida"
        subtitle="{{ $units->count() }} unidade(s) cadastrada(s)"
        icon="ruler"
    >
        <x-app.btn as="a" href="{{ route('unidades.create') }}" icon="plus">Nova Unidade</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    {{-- Tabela --}}
    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Nome', 'Abreviação', 'Produtos', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($units as $i => $u)
                        <tr class="border-b border-mv-border hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $i + 1 }}</td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">{{ $u->name }}</td>
                            <td class="mono px-3.5 py-2.5 text-xs text-mv-text-secondary">{{ $u->abbreviation }}</td>
                            <td class="px-3.5 py-2.5 text-mv-text-secondary">{{ $u->products_count }}</td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('unidades.edit', $u) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('unidades.destroy', $u) }}" onsubmit="return confirm('Excluir esta unidade?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhuma unidade encontrada.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-app.card>
</x-app.layout>
