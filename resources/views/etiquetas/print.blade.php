<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impressão de Etiquetas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: "Inter", "Segoe UI", system-ui, sans-serif; background: #fff; color: #000; }
        @page { margin: 10mm; }
        .labels {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6mm;
        }
        .label {
            border: 1px dashed #bbb;
            border-radius: 3px;
            padding: 6px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 34mm;
        }
        .label .name {
            font-size: 11px;
            font-weight: 600;
            line-height: 1.2;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            width: 100%;
            margin-bottom: 4px;
        }
        .label svg { max-width: 100%; height: auto; }
        .label .code {
            font-family: "Courier New", monospace;
            font-size: 11px;
            letter-spacing: 1px;
            margin-top: 4px;
        }
        .empty { color: #888; padding: 40px; text-align: center; }
        @media screen {
            body { background: #f0f0f0; padding: 20px; }
            .labels { max-width: 1200px; margin: 0 auto; }
        }
    </style>
    @vite(['resources/js/app.js'])
</head>
<body>
    @if ($products->isEmpty())
        <div class="empty">Nenhuma etiqueta selecionada.</div>
    @else
        <div class="labels">
            @foreach ($products as $p)
                <div class="label">
                    <div class="name">{{ $p->name }}</div>
                    <svg data-barcode-element data-barcode-value="{{ $p->barcode }}"></svg>
                    <div class="code">{{ $p->barcode }}</div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>