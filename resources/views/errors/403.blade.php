<!doctype html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 403 · Estoque</title>
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
            background: #1a1e2a;
            border: 1px solid #2a2e3a;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #9ca3af;
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
                <rect x="5" y="11" width="14" height="10" rx="2"/>
                <path d="M8 11V7a4 4 0 018 0v4"/>
            </svg>
        </div>
        <h1>Acesso negado</h1>
        <p>Você não tem permissão para acessar esta página. Se acredita que isso é um erro, fale com o administrador do sistema.</p>
        <div class="actions">
            <a href="/" class="btn btn-primary">Ir para o início</a>
            <a href="javascript:history.back()" class="btn btn-ghost">Voltar</a>
        </div>
        <p class="code">ERRO 403 · FORBIDDEN</p>
    </div>
</body>
</html>
