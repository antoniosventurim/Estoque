export function initSearchableSelects(root = document) {
    root.querySelectorAll('[data-searchable-select]').forEach((box) => {
        if (box.dataset.bound) return;
        box.dataset.bound = '1';

        const input = box.querySelector('input[type="text"]');
        const hidden = box.querySelector('input[type="hidden"]');
        const optionsList = box.querySelector('[data-options]');
        const clearBtn = box.querySelector('[data-clear]');
        const emptyMsg = box.querySelector('[data-empty]');
        const emptyTerm = box.querySelector('[data-empty-term]');
        if (!input || !hidden || !optionsList) return;

        const items = [...optionsList.querySelectorAll('button[data-value]')];
        const required = box.querySelector('input[type="text"]').hasAttribute('required');

        const itemsFilter = () => {
            const q = input.value.trim().toLowerCase();
            const hiddenAll = items.every((b) => b.classList.contains('hidden'));
            if (emptyMsg) emptyMsg.classList.toggle('hidden', !(q && hiddenAll));
            if (emptyTerm) emptyTerm.textContent = input.value;
        };

        const select = (btn) => {
            optionsList.querySelectorAll('button[data-value]').forEach((b) => {
                b.classList.toggle('bg-mv-accent-soft', b.dataset.value === btn.dataset.value);
            });
            hidden.value = btn.dataset.value;
            input.value = btn.dataset.display;
            input.readOnly = true;
            if (clearBtn) clearBtn.classList.remove('hidden');
            optionsList.classList.add('hidden');

            box.dispatchEvent(new CustomEvent('searchable:select', {
                detail: { id: btn.dataset.value, label: btn.dataset.display },
                bubbles: true,
            }));
        };

        const clear = () => {
            hidden.value = '';
            input.value = '';
            input.readOnly = false;
            optionsList.querySelectorAll('button[data-value]').forEach((b) => b.classList.remove('bg-mv-accent-soft'));
            if (clearBtn) clearBtn.classList.add('hidden');
            input.focus();
            openList();
        };

        const openList = () => {
            optionsList.classList.remove('hidden');
            input.readOnly = false;
            input.select();
            items.forEach((b) => b.classList.remove('hidden'));
            itemsFilter();
        };

        // selecionar o valor já definido (filtros persistidos)
        const preset = items.find((b) => b.dataset.value === String(hidden.value));
        if (preset) {
            optionsList.querySelectorAll('button[data-value]').forEach((b) => b.classList.remove('bg-mv-accent-soft'));
            preset.classList.add('bg-mv-accent-soft');
            if (clearBtn) clearBtn.classList.remove('hidden');
        }

        input.addEventListener('focus', () => {
            if (input.readOnly && hidden.value) return;
            openList();
        });

        input.addEventListener('click', () => {
            if (input.readOnly && hidden.value) {
                clear();
            }
        });

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            hidden.value = '';
            items.forEach((b) => {
                b.classList.toggle('hidden', !b.dataset.search.includes(q));
            });
            itemsFilter();
            optionsList.classList.remove('hidden');
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const visible = items.find((b) => !b.classList.contains('hidden'));
                if (visible) {
                    e.preventDefault();
                    select(visible);
                } else if (required) {
                    e.preventDefault();
                }
            }
            if (e.key === 'Escape') optionsList.classList.add('hidden');
        });

        items.forEach((b) => b.addEventListener('click', () => select(b)));

        if (clearBtn) clearBtn.addEventListener('click', (e) => { e.stopPropagation(); clear(); });

        document.addEventListener('click', (e) => {
            if (!box.contains(e.target)) optionsList.classList.add('hidden');
        });

        // bloco envio sem valor quando obrigatório
        const form = box.closest('form');
        if (form && required) {
            form.addEventListener('submit', (e) => {
                if (!hidden.value) {
                    e.preventDefault();
                    optionsList.classList.remove('hidden');
                    input.focus();
                }
            });
        }
    });
}