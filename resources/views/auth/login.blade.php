<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar · Estoque</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-mv-bg p-4 text-mv-text antialiased">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex justify-center">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-white">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" fill="#c8102e"/></svg>
                </div>
                <div>
                    <p class="m-0 text-base font-bold tracking-wider text-white">Meu Estoque</p>
                    <p class="m-0 text-xs text-mv-text-muted">São Mateus — Controle de Estoque</p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-mv-border bg-mv-surface p-6">
            <h1 class="mb-1 text-lg font-semibold text-mv-text">Entrar</h1>
            <p class="mb-5 text-[14px] text-mv-text-secondary">Acesse com seu e-mail e senha</p>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-mv-danger-solid/50 bg-mv-danger-bg p-3 text-[14px] text-mv-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1.5 block text-xs font-medium text-mv-text-secondary">E-mail</label>
                    <input
                        id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="seu@email.com"
                        class="w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2 text-[14px] text-mv-text outline-none transition-colors focus:border-blue-600"
                    >
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-xs font-medium text-mv-text-secondary">Senha</label>
                    <input
                        id="password" type="password" name="password" required
                        placeholder="••••••••"
                        class="w-full rounded-md border border-mv-border bg-mv-surface2 px-2.5 py-2 text-[14px] text-mv-text outline-none transition-colors focus:border-blue-600"
                    >
                </div>

                <input type="hidden" name="remember" value="1">

                <button type="submit"
                    class="flex w-full items-center justify-center rounded-md bg-mv-accent px-4 py-2 text-[14px] font-medium text-white transition-colors hover:bg-mv-accent-hover">
                    Entrar
                </button>
            </form>
        </div>
    </div>
</body>
</html>