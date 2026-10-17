<x-app.layout>
    <x-app.page-header
        title="{{ $role ? 'Editar Perfil' : 'Novo Perfil' }}"
        subtitle="{{ $role ? $role->name : 'Defina quem pode visualizar, criar, editar ou excluir' }}"
        icon="check"
    >
        <x-app.btn as="a" href="{{ route('roles.index') }}" variant="ghost">Voltar</x-app.btn>
    </x-app.page-header>

    <x-app.errors />

    <form method="POST" action="{{ $role ? route('roles.update', $role) : route('roles.store') }}">
        @csrf
        @if ($role)@method('PUT')@endif

        <x-app.card class="mb-5 p-5">
            <div class="flex flex-wrap items-end gap-4">
                <div class="w-full max-w-sm">
                    <x-app.input label="Nome do Perfil *" name="name" value="{{ old('name', $role->name ?? '') }}" placeholder="Ex: Supervisor, Estoquista..." required />
                </div>
                <div class="flex gap-2 pb-0.5">
                    <x-app.btn variant="secondary" size="sm" id="check-all">Marcar todos</x-app.btn>
                    <x-app.btn variant="ghost" size="sm" id="uncheck-all">Desmarcar todos</x-app.btn>
                </div>
            </div>
        </x-app.card>

        <div class="mb-2 flex items-center justify-between">
            <h2 class="m-0 text-sm font-semibold text-mv-text">Permissões por módulo</h2>
            <p class="m-0 text-[13px] text-mv-text-secondary">Clique no chip para marcar</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($modules as $module => $config)
                <x-app.card class="p-4">
                    <p class="mb-3 text-[14px] font-semibold text-mv-text">{{ $config['label'] }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($config['actions'] as $action)
                            @php
                                $permission = $module.'.'.$action;
                            @endphp
                            <label class="cursor-pointer select-none">
                                <input type="checkbox" name="permissions[]" value="{{ $permission }}" class="peer sr-only" {{ $selected->contains($permission) ? 'checked' : '' }}>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-mv-border bg-white/10 px-3 py-1.5 text-[13px] font-medium text-white/70 transition-colors peer-checked:border-mv-accent peer-checked:bg-mv-accent peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-mv-accent">
                                    <x-app.icon name="check" :size="12" />
                                    {{ $actionLabels[$action] ?? $action }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-app.card>
            @endforeach
        </div>

        <div class="mt-5 flex gap-2">
            <x-app.btn type="submit" icon="check">{{ $role ? 'Salvar alterações' : 'Criar Perfil' }}</x-app.btn>
            <x-app.btn as="a" href="{{ route('roles.index') }}" variant="ghost">Cancelar</x-app.btn>
        </div>
    </form>

    <script>
        (function () {
            document.getElementById('check-all')?.addEventListener('click', function () {
                document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) { cb.checked = true; });
            });
            document.getElementById('uncheck-all')?.addEventListener('click', function () {
                document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) { cb.checked = false; });
            });
        })();
    </script>
</x-app.layout>
