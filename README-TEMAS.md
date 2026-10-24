# Temas Visuais — Meu Estoque

Documentação das cores e do layout do sistema para uso em **produção**. O projeto possui dois temas:

| Tema | Status | Característica |
|---|---|---|
| **Claro Corporativo** | 🟢 Padrão atual | Cinza + branco, acento carbôn (azul escuro aparente) |
| **Escuro (legado)** | 🔵 Antigo | Preto/vermelho institucional `#c8102e` (São Mateus) |

O design system é próprio, com prefixo **`mv-`** ("Meu Estoque"), implementado com **Tailwind CSS 4 no modo CSS-first**: todos os tokens ficam no bloco `@theme` de [`resources/css/app.css`](resources/css/app.css) — não existe `tailwind.config.js`.

> **Arquivos-chave:** `resources/css/app.css` (tokens + base), `resources/views/components/app/layout.blade.php` (estrutura), `resources/js/app.js` (paleta de gráficos), `resources/views/errors/*.blade.php` (CSS inline próprio).

---

## Índice

1. [Tema Claro Corporativo (padrão)](#1-tema-claro-corporativo-padrão)
2. [Tema Escuro (legado)](#2-tema-escuro-legado)
3. [Como trocar o tema em produção](#3-como-trocar-o-tema-em-produção)
4. [Componentes de UI](#4-componentes-de-ui)
5. [Gráficos (Chart.js)](#5-gráficos-chartjs)
6. [Páginas de erro](#6-páginas-de-erro)
7. [Cores espalhadas nas views](#7-cores-espalhadas-nas-views)
8. [Tipografia](#8-tipografia)

---

## 1. Tema Claro Corporativo (padrão)

Estilo ERP corporativo: fundo cinza claro, cards brancos, sidebar branca e acento **carbôn** (cinza-azulado `#374151` — dá a aparência de azul escuro).

### 1.1 Design tokens (`@theme`)

```css
/* Design tokens do estoque-layout — corporativo cinza/branco */
@theme {
    --color-mv-bg: #f3f4f6;
    --color-mv-surface: #ffffff;
    --color-mv-surface2: #f9fafb;
    --color-mv-sidebar: #ffffff;
    --color-mv-border: #e5e7eb;
    --color-mv-border-hover: #d1d5db;
    --color-mv-accent: #374151;
    --color-mv-accent-hover: #1f2937;
    --color-mv-accent-dark: #111827;
    --color-mv-accent-soft: #37415114;
    --color-mv-success: #16a34a;
    --color-mv-success-solid: #15803d;
    --color-mv-success-bg: #dcfce7;
    --color-mv-warning: #d97706;
    --color-mv-warning-solid: #b45309;
    --color-mv-warning-bg: #fef3c7;
    --color-mv-danger: #dc2626;
    --color-mv-danger-solid: #b91c1c;
    --color-mv-danger-bg: #fee2e2;
    --color-mv-text: #111827;
    --color-mv-text-secondary: #4b5563;
    --color-mv-text-muted: #9ca3af;
}
```

### 1.2 Referência de cores

| Token | Classe Tailwind | Valor | Uso |
|---|---|---|---|
| `mv-bg` | `bg-mv-bg` | `#f3f4f6` | Fundo da página (área de conteúdo) |
| `mv-surface` | `bg-mv-surface` | `#ffffff` | Cards, topbar, inputs, dropdowns |
| `mv-surface2` | `bg-mv-surface2` | `#f9fafb` | Cabeçalho de tabela, hover de linha, botões secundários |
| `mv-sidebar` | `bg-mv-sidebar` | `#ffffff` | Menu lateral |
| `mv-border` | `border-mv-border` | `#e5e7eb` | Bordas padrão (cards, tabelas, inputs) |
| `mv-border-hover` | `border-mv-border-hover` | `#d1d5db` | Bordas em hover |
| `mv-accent` | `bg-mv-accent` | `#374151` | Botões primários, item ativo do menu, logo, botão Voltar |
| `mv-accent-hover` | `hover:bg-mv-accent-hover` | `#1f2937` | Hover do acento |
| `mv-accent-dark` | `bg-mv-accent-dark` | `#111827` | Acento reforçado |
| `mv-accent-soft` | `bg-mv-accent-soft` | `#37415114` | Fundo suave do acento (14 ̄hex de opacidade) |
| `mv-success` | `text-mv-success` | `#16a34a` | Texto/ícone de sucesso, estoque normal |
| `mv-success-solid` | `bg-mv-success-solid` | `#15803d` | Círculo sólido de sucesso |
| `mv-success-bg` | `bg-mv-success-bg` | `#dcfce7` | Fundo de alerta/badge de sucesso |
| `mv-warning` | `text-mv-warning` | `#d97706` | Atenção, estoque baixo |
| `mv-warning-solid` | `bg-mv-warning-solid` | `#b45309` | Fundo sólido de aviso (variantes) |
| `mv-warning-bg` | `bg-mv-warning-bg` | `#fef3c7` | Fundo de alerta/badge de aviso |
| `mv-danger` | `text-mv-danger` | `#dc2626` | Erro, estoque crítico/zerado |
| `mv-danger-solid` | `bg-mv-danger-solid` | `#b91c1c` | Círculo/botão sólido de erro |
| `mv-danger-bg` | `bg-mv-danger-bg` | `#fee2e2` | Fundo de alerta/badge de erro |
| `mv-text` | `text-mv-text` | `#111827` | Texto principal |
| `mv-text-secondary` | `text-mv-text-secondary` | `#4b5563` | Texto secundário (labels, subtítulos) |
| `mv-text-muted` | `text-mv-text-muted` | `#9ca3af` | Texto sutil (placeholders, "—") |

### 1.3 CSS base (fora do `@theme`)

Também em `resources/css/app.css` — **precisa ser trocado junto** com o tema:

```css
body {
    background-color: #f3f4f6;
    color: #111827;
}

::-webkit-scrollbar-thumb        { background: #d1d5db; }
::-webkit-scrollbar-thumb:hover  { background: #9ca3af; }

/* Checkboxes de impressão (etiquetas/lista) */
#bulk-products input[type="checkbox"],
#print-form input[type="checkbox"] { border: 2px solid #9ca3af; }
#bulk-products input[type="checkbox"]:checked,
#print-form input[type="checkbox"]:checked {
    background-color: #374151;
    border-color: #374151;
}
```

### 1.4 Layout (classes específicas do tema claro)

| Local | Classe (tema claro) |
|---|---|
| `<html>` | `<html lang="pt-BR">` (**sem** classe `dark`) |
| Item ativo do menu | `bg-mv-accent text-white font-medium` |
| Hover de item do menu | `hover:bg-black/[0.05]` |
| Hover de linha de tabela | `hover:bg-black/[0.02]` |
| Hover de opção de select | `hover:bg-black/[0.04]` |
| Barra do logo | `bg-mv-accent` + `border-b border-black/10` |
| Subtítulo do logo | `text-white/70` |
| Botão Sair | `border border-mv-border bg-mv-surface text-mv-text-secondary hover:border-mv-danger hover:text-mv-danger` |
| Foco de inputs | `focus:border-mv-accent` |

### 1.5 Paleta de gráficos (tema claro)

```js
const PALETTE = [
    '#374151', '#0891b2', '#6b7280', '#16a34a', '#d97706',
    '#7c3aed', '#db2777', '#94a3b8',
];

// Eixos (resources/js/app.js)
x.grid:  'rgba(0,0,0,0.06)'
x.ticks: '#6b7280'
y.ticks: '#374151'
```

---

## 2. Tema Escuro (legado)

Tema original do sistema: preto/quase-preto com vermelho institucional `#c8102e` (marca São Mateus).

### 2.1 Design tokens (`@theme`)

```css
/* Design tokens do estoque-layout */
@theme {
    --color-mv-bg: #0f0f0f;
    --color-mv-surface: #1a1a1a;
    --color-mv-surface2: #222222;
    --color-mv-sidebar: #111111;
    --color-mv-border: #2e2e2e;
    --color-mv-border-hover: #3d3d3d;
    --color-mv-accent: #c8102e;
    --color-mv-accent-hover: #e0142f;
    --color-mv-accent-dark: #8f0b20;
    --color-mv-accent-soft: #c8102e22;
    --color-mv-success: #34c45a;
    --color-mv-success-solid: #1a6b35;
    --color-mv-success-bg: #1a6b3522;
    --color-mv-warning: #e09b2a;
    --color-mv-warning-solid: #7a5200;
    --color-mv-warning-bg: #7a520022;
    --color-mv-danger: #e8334a;
    --color-mv-danger-solid: #8f0b20;
    --color-mv-danger-bg: #8f0b2022;
    --color-mv-text: #f0f0f0;
    --color-mv-text-secondary: #999999;
    --color-mv-text-muted: #555555;
}
```

### 2.2 Referência de cores

| Token | Classe Tailwind | Valor | Uso |
|---|---|---|---|
| `mv-bg` | `bg-mv-bg` | `#0f0f0f` | Fundo da página |
| `mv-surface` | `bg-mv-surface` | `#1a1a1a` | Cards, topbar, inputs |
| `mv-surface2` | `bg-mv-surface2` | `#222222` | Cabeçalho de tabela, hover, btn secundário |
| `mv-sidebar` | `bg-mv-sidebar` | `#111111` | Menu lateral |
| `mv-border` | `border-mv-border` | `#2e2e2e` | Bordas padrão |
| `mv-border-hover` | `border-mv-border-hover` | `#3d3d3d` | Bordas em hover |
| `mv-accent` | `bg-mv-accent` | `#c8102e` | Vermelho institucional (botões, logo, ativo do menu) |
| `mv-accent-hover` | `hover:bg-mv-accent-hover` | `#e0142f` | Hover do acento |
| `mv-accent-dark` | `bg-mv-accent-dark` | `#8f0b20` | Acento escuro |
| `mv-accent-soft` | `bg-mv-accent-soft` | `#c8102e22` | Fundo suave (item ativo no menu legado) |
| `mv-success` | `text-mv-success` | `#34c45a` | Sucesso / estoque normal |
| `mv-success-solid` | `bg-mv-success-solid` | `#1a6b35` | Fundo sólido de sucesso |
| `mv-success-bg` | `bg-mv-success-bg` | `#1a6b3522` | Fundo de alerta de sucesso (22 = alpha) |
| `mv-warning` | `text-mv-warning` | `#e09b2a` | Atenção / estoque baixo |
| `mv-warning-solid` | `bg-mv-warning-solid` | `#7a5200` | Fundo sólido de aviso |
| `mv-warning-bg` | `bg-mv-warning-bg` | `#7a520022` | Fundo de alerta de aviso |
| `mv-danger` | `text-mv-danger` | `#e8334a` | Erro / estoque crítico |
| `mv-danger-solid` | `bg-mv-danger-solid` | `#8f0b20` | Fundo sólido de erro |
| `mv-danger-bg` | `bg-mv-danger-bg` | `#8f0b2022` | Fundo de alerta de erro |
| `mv-text` | `text-mv-text` | `#f0f0f0` | Texto principal |
| `mv-text-secondary` | `text-mv-text-secondary` | `#999999` | Texto secundário |
| `mv-text-muted` | `text-mv-text-muted` | `#555555` | Texto sutil |

> Nos tokens escuros, os sufixos `-bg` usam **alpha em hex de 8 dígitos** (`...22` ≈ 13% de opacidade) para os alertas ficarem translúcidos sobre o fundo escuro. No claro, usam cores sólidas claras (`#dcfce7` etc.).

### 2.3 CSS base (tema escuro)

```css
body {
    background-color: #0f0f0f;
    color: #f0f0f0;
}

::-webkit-scrollbar-thumb        { background: #2e2e2e; }
::-webkit-scrollbar-thumb:hover  { background: #3d3d3d; }

/* Checkboxes */
border: 2px solid #4a4e53;
:checked { background-color: #2563eb; border-color: #2563eb; }
```

### 2.4 Layout (classes específicas do tema escuro)

| Local | Classe (tema escuro legado) |
|---|---|
| `<html>` | `<html lang="pt-BR" class="dark">` (**obrigatório** — o Bladewind UI usa `.dark` para os componentes) |
| Item ativo do menu | `bg-mv-accent-soft text-mv-accent-hover font-medium` (submembro: `text-mv-accent bg-mv-accent-soft`) |
| Hover de item do menu | `hover:bg-white/5` |
| Hover de linha de tabela | `hover:bg-white/[0.02]` |
| Hover de opção de select | `hover:bg-white/5` |
| Barra do logo | `bg-mv-accent` (vermelho) + `border-b border-mv-border` |
| Subtítulo do logo | `text-red-200` |
| Botão Sair | `bg-mv-danger-solid text-white hover:opacity-90` |
| Foco de inputs | `focus:border-blue-600` |

### 2.5 Paleta de gráficos (tema escuro)

```js
const PALETTE = [
    '#c8102e', '#e05a2f', '#e09b2a', '#34c45a', '#34a6c4',
    '#5a7ce0', '#9a5ae0', '#e05ad1',
];

// Eixos
x.grid:  'rgba(255,255,255,0.06)'
x.ticks: '#999999'
y.ticks: '#f0f0f0'
```

---

## 3. Como trocar o tema em produção

Passo a passo para aplicar um dos temas (a troca é **somente visual** — nenhuma lógica muda):

### Passo 1 — `resources/css/app.css`

1. Substitua o bloco `@theme` inteiro pelo do tema desejado (seções 1.1 ou 2.1).
2. Atualize o `body`, a `scrollbar` e as `checkboxes` (seções 1.3 ou 2.3).
3. Mantenha as linhas de import (`@import` das fontes e do Tailwind).

### Passo 2 — `<html>` no layout

- **Claro:** `<html lang="pt-BR">`
- **Escuro:** `<html lang="pt-BR" class="dark">` (em `components/app/layout.blade.php` — a classe `dark` também é usada pelas variantes escuras do Bladewind UI)

### Passo 3 — classes nas views

Itens que são específicos de cada tema:

| Arquivo | Tema claro | Tema escuro |
|---|---|---|
| `layout.blade.php` (nav ativa) | `bg-mv-accent text-white` | `bg-mv-accent-soft text-mv-accent-hover` |
| `layout.blade.php` (hover nav) | `hover:bg-black/[0.05]` | `hover:bg-white/5` |
| `layout.blade.php` (logo borda) | `border-black/10` | `border-mv-border` |
| `layout.blade.php` (subtítulo logo) | `text-white/70` | `text-red-200` |
| `layout.blade.php` (Sair) | `border-mv-border bg-mv-surface ... hover:text-mv-danger` | `bg-mv-danger-solid text-white hover:opacity-90` |
| Todas as tabelas (`hover` de linha) | `hover:bg-black/[0.02]` | `hover:bg-white/[0.02]` |
| `searchable-select.blade.php` (hover) | `hover:bg-black/[0.04]` | `hover:bg-white/5` |
| Inputs (`focus`) | `focus:border-mv-accent` | `focus:border-blue-600` |

### Passo 4 — `resources/js/app.js`

Troque a constante `PALETTE` e as cores dos eixos (seções 1.5 ou 2.5).

### Passo 5 — páginas de erro

`resources/views/errors/{403,404,419,500,503}.blade.php` possuem CSS inline próprio — aplique a paleta da seção 6.

### Passo 6 — build

```bash
npm run build
```

> **Atalho prático:** se no seu projeto de produção os dois temas forem alternáveis, a estratégia mais simples é manter os dois blocos `@theme` em arquivos separados (`app-light.css` / `app-dark.css`) e trocar o `@import` no build, mantendo as classes das views fixas nas variantes tokenizadas (`bg-mv-*`), evitando os valores fixos da seção 3.

---

## 4. Componentes de UI

### Botões (`components/app/btn.blade.php`)

| Variant | Classes | Tema claro | Tema escuro |
|---|---|---|---|
| `primary` | `bg-mv-accent text-white hover:bg-mv-accent-hover` | Carbôn | Vermelho |
| `secondary` | `bg-mv-surface2 text-mv-text border border-mv-border` | Cinza claro | Cinza escuro |
| `danger` | `bg-mv-danger-solid text-white hover:opacity-90` | Vermelho sólido | Vermelho sólido |
| `ghost` (Voltar/Cancelar/Limpar) | `bg-mv-accent text-white border border-mv-accent hover:bg-mv-accent-hover hover:text-white` | Carbôn | Vermelho |

### Badges de status de estoque (`stock-badge`)

| Status | Classes |
|---|---|
| Zerado / Crítico | `bg-mv-danger-bg text-mv-danger` |
| Baixo | `bg-mv-warning-bg text-mv-warning` |
| Excesso | `bg-purple-50 text-purple-700` |
| Normal | `bg-mv-success-bg text-mv-success` |

### Badges de movimentação (`badge`)

| Tipo | Classes | Label |
|---|---|---|
| `in` | `bg-mv-success-bg text-mv-success` | Entrada |
| `out` | `bg-mv-danger-bg text-mv-danger` | Saída |
| `adjust` | `bg-mv-warning-bg text-mv-warning` | Ajuste |

### Cards e demais

- **Card:** `rounded-lg border border-mv-border bg-mv-surface`
- **Alertas de erro:** `border border-mv-danger-solid/50 bg-mv-danger-bg text-mv-danger`
- **Alertas de sucesso:** `border border-mv-success-solid/50 bg-mv-success-bg text-mv-success`
- **Paginação:** texto `text-mv-text-secondary`, links em `bg-mv-surface2`

---

## 5. Gráficos (Chart.js)

Paletas por tema (seções 1.5 e 2.5), aplicadas em `resources/js/app.js`:

| Elemento | Tema claro | Tema escuro |
|---|---|---|
| Barras | `['#374151', '#0891b2', '#6b7280', '#16a34a', '#d97706', '#7c3aed', '#db2777', '#94a3b8']` | `['#c8102e', '#e05a2f', '#e09b2a', '#34c45a', '#34a6c4', '#5a7ce0', '#9a5ae0', '#e05ad1']` |
| Grid (eixo X) | `rgba(0,0,0,0.06)` | `rgba(255,255,255,0.06)` |
| Ticks X | `#6b7280` | `#999999` |
| Ticks Y | `#374151` | `#f0f0f0` |

---

## 6. Páginas de erro

`resources/views/errors/{403,404,419,500,503}.blade.php` — cada página tem um CSS inline autossuficiente (não usa os tokens `mv-`).

| Elemento | Tema claro | Tema escuro (legado) |
|---|---|---|
| Fundo da página | `#f3f4f6` | `#0f1117` |
| Texto principal | `#111827` | `#e4e6eb` |
| Card | `#ffffff` | `#181b23` |
| Borda do card | `#e5e7eb` | `#2a2e3a` |
| Ícone (fundo) | `#f3f4f6` | `#1a1e2a` |
| Texto secundário | `#6b7280` | `#9ca3af` |
| Botão primário | `#374151` | `#c8102e` |
| Código do erro | `#9ca3af` | `#4b5060` |
| Variante 419/503 (aviso) | fundo `#fef3c7` / texto `#b45309` | fundo `#2a2418` / texto `#e8a033` |
| Variante 500 (erro) | fundo `#fee2e2` / texto `#b91c1c` | fundo `#2a1a1e` / texto `#e8334a` |

---

## 7. Cores espalhadas nas views

Algumas cores ficam fora dos tokens (classes utilitárias ou `style` inline). Valores por tema:

| Onde | Tema claro | Tema escuro (legado) |
|---|---|---|
| Alerta de erro (dashboard, error-card, barcode-search) | `background:#fee2e2; border:1px solid #fca5a5` | `background:#8f0b2022; border:1px solid #e8334a55` |
| Alerta de sucesso (entrada, inventário) | `background:#dcfce7; border-color:#16a34a55` | `background:#1a6b3522; border-color:#34c45a55` |
| Caixa de preview do inventário | `background:#f9fafb` (borda `#d1d5db` + 55) | `background:#222` (borda `#2e2e2e` + 55) |
| Cores do preview (JS) | perigo `#dc2626`, sobra `#16a34a`, neutro `#d1d5db` | perigo `#e8334a`, sobra `#34c45a`, neutro `#2e2e2e` |
| Avatar de usuário (lista de usuários) | admin `#374151` / operador `#6b7280` | admin `#c8102e` / operador `#7c3aed` |
| Barra % em relatórios | `#d97706` | `#e09b2a` |
| Pill "Parcial" (solicitações) | `bg-blue-50 text-blue-700` | `bg-blue-500/10 text-blue-400` |
| Pill "Recebida" | `bg-green-500/10 text-green-700` | `bg-green-500/10 text-green-400` |
| Pills no log de atividade (criado/alterado/excluído) | `bg-green-500/10 text-green-700` / `bg-blue-50 text-blue-700` / `bg-red-500/10 text-red-700` | `bg-green-500/10 text-green-500` / `bg-blue-500/10 text-blue-400` / `bg-red-500/10 text-red-400` |
| Alertas de erro/sucesso na página de saída | `border-*-500/40 bg-*-500/10 text-*-700` | `border-*-500/40 bg-*-500/10 text-*-400` |
| Checkbox (`#bulk-products` / `#print-form`) | borda `#9ca3af`, marcado `#374151` | borda `#4a4e53`, marcado `#2563eb` |

> **Dica:** prefira tokens (`text-mv-danger`, `bg-mv-danger-bg`) em código novo — assim a troca de tema é automática.

---

## 8. Tipografia

| Recurso | Valor |
|---|---|
| Fonte principal | **Inter** (400, 500, 600, 700) via Google Fonts |
| Fonte monoespaçada | **JetBrains Mono** (400, 500) — classe `.mono` (códigos de barras, IDs, datas) |
| Tamanho base | `15px` (`body`) |
| Textos de tabela/label | `13px`–`14px` |

```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap');
```

---

*Design system `mv-` — Tailwind CSS 4 (`@theme` em `resources/css/app.css`). Tema atual: Claro Corporativo. Tema legado: Escuro (vermelho `#c8102e`).*
