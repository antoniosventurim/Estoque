<x-app.layout>
    <div class="flex min-h-[55vh] items-center justify-center py-10">
        <x-app.card class="w-full max-w-md p-8 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full border border-mv-border bg-mv-surface2 text-mv-text-secondary">
                <x-app.icon name="lock" :size="28" />
            </div>
            <h1 class="m-0 text-2xl font-semibold text-mv-text">Acesso negado</h1>
            <p class="mt-2 text-[14px] text-mv-text-secondary">
                Você não tem permissão para acessar esta página. Se acredita que isso é um erro, fale com o administrador do sistema.
            </p>
            <div class="mt-5 flex justify-center gap-2">
                <x-app.btn as="a" href="{{ route('dashboard') }}" icon="dashboard">Ir para o Dashboard</x-app.btn>
                <x-app.btn as="a" href="{{ url()->previous() }}" variant="ghost">Voltar</x-app.btn>
            </div>
        </x-app.card>
    </div>
</x-app.layout>
