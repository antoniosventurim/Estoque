import { initSearchableSelects } from './searchable-select';

const PALETTE = [
    '#c8102e', '#e05a2f', '#e09b2a', '#34c45a', '#34a6c4',
    '#5a7ce0', '#9a5ae0', '#e05ad1',
];

document.addEventListener('DOMContentLoaded', () => {
    initSearchableSelects();
    initProductNameSearch();
    initOutflowCharts();
    initBarcodeLabels();
});

function initProductNameSearch() {
    document.querySelectorAll('[data-product-name-search]').forEach((box) => {
        box.addEventListener('searchable:select', (e) => {
            const route = box.dataset.route;
            const id = e.detail?.id;
            if (!route || !id) return;

            window.location = `${route}?barcode=${encodeURIComponent(id)}`;
        });
    });
}

function initOutflowCharts() {
    document.querySelectorAll('[data-outflow-chart]').forEach(async (canvas) => {
        const el = document.getElementById(canvas.dataset.outflowChart);
        if (!el) return;

        let labels;
        let data;
        try {
            const parsed = JSON.parse(el.textContent);
            labels = parsed.labels ?? [];
            data = parsed.data ?? [];
        } catch {
            return;
        }

        if (!labels.length) return;

        const { Chart, BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend } =
            await import('chart.js/auto');

        Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: data.map((_, i) => PALETTE[i % PALETTE.length]),
                    borderRadius: 5,
                    barPercentage: 0.65,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        displayColors: false,
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.x} un`,
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.06)' },
                        ticks: { color: '#999999', precision: 0 },
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: '#f0f0f0',
                            font: { size: 12 },
                            autoSkip: false,
                        },
                    },
                },
            },
        });
    });
}

async function initBarcodeLabels() {
    const elements = document.querySelectorAll('[data-barcode-element]');
    if (!elements.length) return;

    const JsBarcode = (await import('jsbarcode')).default;

    elements.forEach((el) => {
        try {
            JsBarcode(el, el.dataset.barcodeValue, {
                format: 'CODE128',
                displayValue: false,
                height: 34,
                width: 1.5,
                margin: 0,
            });
        } catch (e) {
            el.outerHTML = '<span style="color:#666">código inválido</span>';
        }
    });

    setTimeout(() => window.print(), 200);
}