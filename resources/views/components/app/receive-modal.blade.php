@props(['purchaseRequest'])

@php
    $items = $purchaseRequest->items->filter(fn ($item) => ! $item->isFullyReceived());
    $modalId = 'receive-modal-' . $purchaseRequest->id;
@endphp

<div id="{{ $modalId }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4" data-receive-modal>
    <div class="w-full max-w-lg rounded-lg border border-mv-border bg-mv-surface shadow-2xl" data-receive-panel>
        {{-- Passo 1: todos presentes? --}}
        <div data-receive-step="1">
            <div class="border-b border-mv-border px-5 py-4">
                <h3 class="m-0 text-[15px] font-semibold text-mv-text">Confirmar Recebimento</h3>
                <p class="mt-1 text-[14px] text-mv-text-secondary">Todos os {{ $items->count() }} item(ns) da solicitação #{{ $purchaseRequest->id }} estão presentes?</p>
            </div>
            <div class="flex flex-col gap-2 px-5 py-4">
                <button type="button" data-receive-all
                    class="w-full rounded-md bg-mv-accent px-4 py-2.5 text-left text-[14px] font-medium text-white transition-opacity hover:opacity-90">
                    Sim, recebi tudo — atualizar estoque
                </button>
                <button type="button" data-receive-partial
                    class="w-full rounded-md border border-mv-border bg-mv-surface2 px-4 py-2.5 text-left text-[14px] font-medium text-mv-text transition-colors hover:border-mv-border-hover">
                    Não, há itens faltando — informar quantidades
                </button>
            </div>
            <div class="border-t border-mv-border px-5 py-3 text-right">
                <button type="button" data-receive-cancel class="rounded-md px-3 py-1.5 text-[14px] text-mv-text-secondary hover:text-mv-text">Cancelar</button>
            </div>
        </div>

        {{-- Passo 2: informar faltantes --}}
        <div data-receive-step="2" class="hidden">
            <div class="border-b border-mv-border px-5 py-4">
                <h3 class="m-0 text-[15px] font-semibold text-mv-text">Recebimento Parcial</h3>
                <p class="mt-1 text-[14px] text-mv-text-secondary">Informe a quantidade <strong>recebida</strong> de cada item. Zere ou coloque 0 nos que faltaram.</p>
            </div>
            <form method="POST" action="{{ route('solicitacoes.deliver', $purchaseRequest) }}" data-receive-form>
                @csrf
                <div class="max-h-[320px] overflow-y-auto">
                    <table class="w-full border-collapse text-[14px]">
                        <thead>
                            <tr class="border-b border-mv-border bg-mv-surface2">
                                <th class="px-4 py-2 text-left text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Produto</th>
                                <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Solicitado</th>
                                <th class="px-4 py-2 text-center text-[13px] font-medium uppercase tracking-wider text-mv-text-secondary">Recebido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr class="border-b border-mv-border">
                                    <td class="px-4 py-2.5 text-mv-text">{{ $item->product->name ?? '—' }}</td>
                                    <td class="px-4 py-2.5 text-center font-semibold text-mv-text-secondary">{{ $item->missing_quantity }}</td>
                                    <td class="px-4 py-2.5 text-center">
                                        <input type="number" name="received[{{ $item->id }}]" value="{{ $item->missing_quantity }}" min="0" max="{{ $item->missing_quantity }}" step="1" data-received-input
                                            class="w-20 rounded-md border border-mv-border bg-mv-surface2 px-2 py-1.5 text-center text-[14px] text-mv-text outline-none">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end gap-2 border-t border-mv-border px-5 py-3">
                    <button type="button" data-receive-back class="rounded-md border border-mv-border bg-mv-surface2 px-4 py-2 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover">Voltar</button>
                    <button type="submit" class="rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-opacity hover:opacity-90">Confirmar Recebimento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('{{ $modalId }}');
        if (!modal) return;

        const step1 = modal.querySelector('[data-receive-step="1"]');
        const step2 = modal.querySelector('[data-receive-step="2"]');
        const form = modal.querySelector('[data-receive-form]');

        function showStep(n) {
            step1.classList.toggle('hidden', n !== 1);
            step2.classList.toggle('hidden', n !== 2);
        }

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            showStep(1);
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.querySelectorAll('[data-open-receive="{{ $purchaseRequest->id }}"]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                openModal();
            });
        });

        modal.querySelector('[data-receive-cancel]').addEventListener('click', closeModal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        modal.querySelector('[data-receive-all]').addEventListener('click', function () {
            modal.querySelectorAll('[data-received-input]').forEach(function (input) {
                input.disabled = true;
            });
            form.submit();
        });

        modal.querySelector('[data-receive-partial]').addEventListener('click', function () {
            showStep(2);
        });

        modal.querySelector('[data-receive-back]').addEventListener('click', function () {
            showStep(1);
        });
    })();
</script>
