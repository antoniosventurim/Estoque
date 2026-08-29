<x-app.layout>
    <x-app.page-header
        title="{{ $user ? 'Editar Usuário' : 'Novo Usuário' }}"
        subtitle="{{ $user ? $user->name : 'Cadastre um acesso ao sistema' }}"
        icon="users"
    >
        <x-app.btn as="a" href="{{ route('usuarios') }}" variant="ghost">Voltar</x-app.btn>
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

            <div class="flex items-end sm:col-span-2">
                <x-bladewind::checkbox
                    name="is_admin"
                    value="1"
                    label="Administrador"
                    color="blue"
                    add_clearing="false"
                    :checked="(bool) old('is_admin', $user->is_admin ?? false)"
                />
            </div>

            <div class="flex gap-2 sm:col-span-2">
                <x-app.btn type="submit" icon="check">{{ $user ? 'Salvar alterações' : 'Cadastrar Usuário' }}</x-app.btn>
                <x-app.btn as="a" href="{{ route('usuarios') }}" variant="ghost">Cancelar</x-app.btn>
            </div>
        </form>
    </x-app.card>
</x-app.layout>