<x-app.layout>
    <x-app.page-header
        title="Usuários"
        subtitle="Gerenciar acesso ao sistema"
        icon="users"
    >
        <x-app.btn as="a" href="{{ route('usuarios.create') }}" icon="plus">Novo Usuário</x-app.btn>
    </x-app.page-header>

    <x-app.flash />

    @if (session('error'))
        <div class="mb-4 rounded-md border border-mv-danger-solid/50 bg-mv-danger-bg p-3 text-[14px] text-mv-danger">{{ session('error') }}</div>
    @endif

    <x-app.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-[14px]">
                <thead>
                    <tr class="border-b border-mv-border">
                        @foreach (['Usuário', 'E-mail', 'Perfil', 'Status', 'Ações'] as $h)
                            <th class="whitespace-nowrap px-4 py-2.5 {{ $h === 'Ações' ? 'text-center' : 'text-left' }} text-[13px] uppercase tracking-wider text-mv-text-secondary">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php
                            $parts = explode(' ', trim($u->name));
                            $init = strtoupper(Str::substr($parts[0], 0, 1) . Str::substr($parts[1] ?? '', 0, 1));
                            $role = $u->is_admin ? 'Administrador' : 'Operador';
                        @endphp
                        <tr class="border-b border-mv-border">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-[30px] w-[30px] flex-shrink-0 items-center justify-center rounded-full text-[13px] font-bold text-white" style="background: {{ $u->is_admin ? '#c8102e' : '#7c3aed' }}">{{ $init }}</div>
                                    <span class="font-medium text-mv-text">{{ $u->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-mv-text-secondary">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-mv-text-secondary">{{ $role }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-mv-success-bg px-2 py-0.5 text-[13px] font-medium text-mv-success">Ativo</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-app.btn as="a" href="{{ route('usuarios.edit', $u) }}" size="sm" variant="secondary" icon="edit">Editar</x-app.btn>
                                    <form method="POST" action="{{ route('usuarios.destroy', $u) }}" onsubmit="return confirm('Excluir este usuário?')">
                                        @csrf @method('DELETE')
                                        <x-app.btn type="submit" size="sm" variant="danger" icon="trash">Excluir</x-app.btn>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-app.card>
</x-app.layout>