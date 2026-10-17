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

    <div id="no-permission-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-lg border border-mv-border bg-mv-surface shadow-2xl">
            <div class="flex items-center gap-3 border-b border-mv-border px-5 py-4">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-mv-accent/10 text-mv-accent">
                    <x-app.icon name="lock" :size="18" />
                </span>
                <h3 class="m-0 text-[15px] font-semibold text-mv-text">Sem permissão</h3>
            </div>
            <div class="px-5 py-4 text-[14px] leading-relaxed text-mv-text-secondary">
                Você não tem permissão para acessar esta página.
                Se acredita que isso é um erro, fale com o administrador do sistema.
            </div>
            <div class="flex justify-end gap-2 border-t border-mv-border px-5 py-3">
                <button type="button" id="close-no-permission"
                    class="rounded-md border border-mv-border bg-mv-surface2 px-4 py-2 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover">
                    Fechar
                </button>
                <a href="{{ route('dashboard') }}"
                    class="rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-opacity hover:opacity-90">
                    Ir para o Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('close-no-permission')?.addEventListener('click', function () {
            document.getElementById('no-permission-modal')?.classList.add('hidden');
        });
    </script>
</x-app.layout>
