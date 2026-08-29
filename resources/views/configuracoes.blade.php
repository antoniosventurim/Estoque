<x-app.layout>
    <x-app.page-header
        title="Configurações"
        icon="settings"
    />

    @if (session('success'))
        <div class="mb-4 rounded-md border border-mv-success-solid/50 bg-mv-success-bg p-3 text-[14px] text-mv-success">
            <span class="inline-flex items-center gap-2"><x-app.icon name="check" :size="14" />{{ session('success') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('configuracoes.save') }}" class="max-w-xl space-y-4">
        @csrf

        <x-app.card class="p-5">
            <h3 class="mb-4 mt-0 text-[14px] font-semibold text-mv-text">Dados da Empresa</h3>
            <div class="flex flex-col gap-3.5">
                <x-app.input label="Nome da empresa" name="empresa_nome" value="{{ old('empresa_nome', $empresa_nome) }}" />
                <x-app.input label="E-mail de contato" value="{{ auth()->user()->email }}" readonly />
                <x-app.input label="CNPJ" name="empresa_cnpj" value="{{ old('empresa_cnpj', $empresa_cnpj) }}" mono />
                <x-app.input label="Telefone" name="empresa_telefone" value="{{ old('empresa_telefone', $empresa_telefone) }}" />
                <x-app.input label="Endereço" name="empresa_endereco" value="{{ old('empresa_endereco', $empresa_endereco) }}" />
                <x-app.input label="Estoque mínimo padrão" name="estoque_minimo_padrao" type="number" min="0" value="{{ old('estoque_minimo_padrao', $estoque_minimo_padrao) }}" />
                <div>
                    <label for="alerta_estoque_acima_minimo" class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Alerta de estoque próximo ao mínimo (%)</label>
                    <input id="alerta_estoque_acima_minimo" name="alerta_estoque_acima_minimo" type="number" min="0" max="1000" value="{{ old('alerta_estoque_acima_minimo', $alerta_estoque_acima_minimo) }}" class="w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2.5 text-[14px] text-mv-text outline-none transition-colors focus:border-mv-border">
                    <p class="mt-1 text-[13px] text-mv-text-muted">Ex.: 50 = alerta quando o estoque ficar até 50% acima do mínimo.</p>
                </div>
            </div>
        </x-app.card>

        @if ($errors->any())
            <div class="rounded-md border border-mv-danger-solid/50 bg-mv-danger-bg p-3 text-[14px] text-mv-danger">{{ $errors->first() }}</div>
        @endif

        <x-app.btn type="submit" icon="check">Salvar configurações</x-app.btn>
    </form>
</x-app.layout>
