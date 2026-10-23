<!doctype html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 419 · Estoque</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, sans-serif;
            background: #0f1117;
            color: #e4e6eb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            background: #181b23;
            border: 1px solid #2a2e3a;
            border-radius: 12px;
            padding: 48px 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
        }
        .icon-wrap {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #2a2418;
            border: 1px solid #e8a03333;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #e8a033;
        }
        h1 { font-size: 22px; font-weight: 600; margin-bottom: 8px; }
        p { font-size: 14px; color: #9ca3af; line-height: 1.6; margin-bottom: 28px; }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: opacity .15s;
        }
        .btn:hover { opacity: .85; }
        .btn-primary { background: #c8102e; color: #fff; }
        .btn-ghost { background: transparent; color: #9ca3af; border: 1px solid #2a2e3a; }
        .code { font-size: 11px; color: #4b5060; margin-top: 24px; letter-spacing: .5px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </div>
        <h1>Sessão expirada</h1>
        <p>Sua sessão expirou ou o formulário ficou obsoleto. Faça login novamente e tente de volta.</p>
        <div class="actions">
            <a href="/login" class="btn btn-primary">Fazer login</a>
            <a href="/" class="btn btn-ghost">Ir para o início</a>
        </div>
        <p class="code">ERRO 419 · PAGE EXPIRED</p>
    </div>
</body>
</html>
