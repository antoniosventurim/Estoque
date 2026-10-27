{{-- Modal global de confirmação (exclusões).
      <form data-confirm-message="A exclusão irá remover..." data-confirm-name="João" data-confirm-title="Excluir usuário" data-confirm-accept="Excluir"> --}}
<div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4">
    <div class="w-full max-w-sm overflow-hidden rounded-xl border border-mv-border bg-mv-surface shadow-2xl">
        <div class="px-6 pt-7 pb-5 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-mv-danger-bg text-mv-danger">
                <x-app.icon name="trash" :size="22" />
            </div>
            <h3 data-confirm-title class="m-0 text-[16px] font-semibold text-mv-text">Excluir</h3>
            <p data-confirm-name class="mt-1.5 text-[15px] font-semibold text-mv-danger"></p>
            <p data-confirm-message class="mt-2 text-[14px] leading-relaxed text-mv-text-secondary"></p>
            <p class="mt-3 inline-block rounded-md bg-mv-danger-bg px-3 py-1.5 text-[13px] font-medium text-mv-danger-solid">
                Esta ação não pode ser desfeita.
            </p>
        </div>
        <div class="flex gap-3 border-t border-mv-border bg-mv-surface2 px-6 py-4">
            <button type="button" data-confirm-cancel class="flex-1 rounded-lg border border-mv-border bg-mv-surface px-4 py-2.5 text-[14px] font-medium text-mv-text-secondary transition-colors hover:border-mv-border-hover hover:text-mv-text">Cancelar</button>
            <button type="button" data-confirm-accept class="flex-1 rounded-lg bg-mv-danger px-4 py-2.5 text-[14px] font-semibold text-white transition-colors hover:bg-mv-danger-solid">Excluir</button>
        </div>
    </div>
</div>

<script>
    (function () {
        var modal = document.getElementById('confirm-modal');
        if (!modal) return;

        var titleEl = modal.querySelector('[data-confirm-title]');
        var nameEl = modal.querySelector('[data-confirm-name]');
        var messageEl = modal.querySelector('[data-confirm-message]');
        var acceptBtn = modal.querySelector('[data-confirm-accept]');
        var cancelBtn = modal.querySelector('[data-confirm-cancel]');
        var pendingForm = null;

        function open(attrs) {
            titleEl.textContent = attrs.title || 'Excluir';
            nameEl.textContent = attrs.name || '';
            nameEl.classList.toggle('hidden', !attrs.name);
            messageEl.textContent = attrs.message || '';
            acceptBtn.textContent = attrs.accept || 'Excluir';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function close() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        }

        document.addEventListener('submit', function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.hasAttribute('data-confirm-message')) return;

            event.preventDefault();
            pendingForm = form;
            open({
                title: form.getAttribute('data-confirm-title'),
                name: form.getAttribute('data-confirm-name'),
                message: form.getAttribute('data-confirm-message'),
                accept: form.getAttribute('data-confirm-accept')
            });
        });

        acceptBtn.addEventListener('click', function () {
            if (!pendingForm) return close();
            var form = pendingForm;
            pendingForm = null;
            close();
            HTMLFormElement.prototype.submit.call(form);
        });

        cancelBtn.addEventListener('click', close);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) close();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });
    })();
</script>
