<x-app.layout>
    <x-app.page-header
        title="{{ $user ? 'Editar Usuário' : 'Novo Usuário' }}"
        subtitle="{{ $user ? $user->name : 'Cadastre um acesso ao sistema' }}"
        icon="users"
    >
        <x-app.btn as="a" href="{{ route('usuarios') }}" variant="back">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <x-app.card class="p-5">
        <form method="POST" action="{{ $user ? route('usuarios.update', $user) : route('usuarios.store') }}" class="grid grid-cols-1 gap-3.5 sm:grid-cols-2">
            @csrf
            @if ($user)@method('PUT')@endif

            <div class="sm:col-span-2">
                <x-app.input label="Nome *" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Ex: Maria da Silva" required autofocus />
            </div>

            <div class="sm:col-span-2">
                <x-app.input label="E-mail *" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" placeholder="ex: maria@empresa.com" required />
            </div>

            <x-app.input label="Senha" name="password" type="password" placeholder="Mínimo 8 caracteres" :required="! $user" />
            <x-app.input label="Confirmar senha" name="password_confirmation" type="password" placeholder="Repita a senha" />

            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Nível de acesso</label>
                <div class="flex flex-wrap items-center gap-2">
                    <label class="cursor-pointer select-none">
                        <input type="checkbox" name="is_admin" value="1" class="peer sr-only" {{ (bool) old('is_admin', $user->is_admin ?? false) ? 'checked' : '' }}>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-mv-border bg-white/10 px-3 py-1.5 text-[13px] font-medium text-white/70 transition-colors peer-checked:border-mv-accent peer-checked:bg-mv-accent peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-mv-accent">
                            <x-app.icon name="check" :size="12" />
                            Acesso total (Administrador)
                        </span>
                    </label>
                </div>
                <p class="mt-1.5 text-xs text-mv-text-muted">Com acesso total o usuário ignora as permissões dos perfis abaixo.</p>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Perfis de acesso</label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($roles as $role)
                        <label class="cursor-pointer select-none">
                            <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="peer sr-only" {{ $selectedRoles->contains($role->name) ? 'checked' : '' }}>
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-mv-border bg-white/10 px-3 py-1.5 text-[13px] font-medium text-white/70 transition-colors peer-checked:border-mv-accent peer-checked:bg-mv-accent peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-mv-accent">
                                <x-app.icon name="check" :size="12" />
                                {{ $role->name }}
                                <span class="opacity-70">· {{ $role->permissions->count() }} permissões</span>
                            </span>
                        </label>
                    @empty
                        <p class="rounded-md border border-mv-border bg-mv-surface2 px-3 py-2 text-[13px] text-mv-text-muted">
                            Nenhum perfil cadastrado ainda — crie em Configurações → Permissões.
                        </p>
                    @endforelse
                </div>
                <p class="mt-1.5 text-xs text-mv-text-muted">O usuário terá a união das permissões dos perfis marcados. Clique no chip para marcar.</p>
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <x-app.btn type="submit" icon="check">{{ $user ? 'Salvar alterações' : 'Cadastrar Usuário' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('usuarios') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>