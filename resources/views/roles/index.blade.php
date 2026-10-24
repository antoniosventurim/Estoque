<x-app.layout>
    <x-app.page-header
        title="Permissões e Perfis"
        subtitle="{{ $roles->count() }} perfil(is) cadastrado(s)"
        icon="check"
    >
        <x-app.btn as="a" href="{{ route('configuracoes') }}" variant="ghost">Configurações</x-app.btn>
        <x-app.btn as="a" href="{{ route('roles.create') }}" icon="plus">Novo Perfil</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border bg-mv-surface2">
                        @foreach (['#', 'Perfil', 'Permissões', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-3.5 py-2.5 {{ in_array($h, ['Permissões', 'Ações']) ? 'text-center' : 'text-left' }} text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $i => $role)
                        <tr class="border-b border-mv-border hover:bg-black/[0.02] dark:hover:bg-white/[0.02]">
                            <td class="px-3.5 py-2.5 text-xs text-mv-text-muted">{{ $i + 1 }}</td>
                            <td class="px-3.5 py-2.5 font-medium text-mv-text">
                                {{ $role->name }}
                                @if ($role->name === $builtinRole)
                                    <span class="ml-1.5 rounded-full bg-mv-accent-soft px-2 py-0.5 text-[12px] font-semibold text-mv-accent-hover">Padrão</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5 text-center text-mv-text-secondary">
                                @if ($role->name === $builtinRole)
                                    Todas
                                @else
                                    {{ $role->permissions_count }}
                                @endif
                            </td>
                            <td class="px-3.5 py-2.5">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('roles.edit', $role) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    @if ($role->name !== $builtinRole)
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" onsubmit="return confirm('Excluir este perfil? Usuários que o utilizam perderão as permissões dele.')">
                                            @csrf @method('DELETE')
                                            <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3.5 py-10 text-center text-[14px] text-mv-text-muted">Nenhum perfil cadastrado. Rode <code class="mono text-xs">php artisan db:seed --class=PermissionSeeder</code>.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-app.card>
</x-app.layout>
