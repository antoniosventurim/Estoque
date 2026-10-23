# Meu Estoque — São Mateus

Sistema web de **controle de estoque** desenvolvido em **Laravel 12** (PHP ^8.2), com interface em Blade + Tailwind CSS 4, voltado para uma instituição de ensino (cursos, laboratórios, setores e administração).

O sistema gerencia produtos com código de barras, movimentações de entrada/saída/inventário, centros de custo, funcionários, etiquetas impressas, relatórios e lista de compra (reposição).

---

## Índice

1. [Stack Tecnológica](#1-stack-tecnológica)
2. [Instalação com Docker](#2-instalação-com-docker)
3. [Acesso Inicial (Seed)](#3-acesso-inicial-seed)
4. [Autenticação e Permissões](#4-autenticação-e-permissões)
5. [Dashboard](#5-dashboard)
6. [Produtos](#6-produtos)
7. [Código de Barras](#7-código-de-barras)
8. [Movimentações — Saída](#8-movimentações--saída)
9. [Movimentações — Entrada](#9-movimentações--entrada)
10. [Inventário (Ajuste de Estoque)](#10-inventário-ajuste-de-estoque)
11. [Status de Estoque](#11-status-de-estoque)
12. [Lista de Compra (Reposição)](#12-lista-de-compra-reposição)
13. [Etiquetas](#13-etiquetas)
14. [Histórico de Movimentações](#14-histórico-de-movimentações)
15. [Relatórios](#15-relatórios)
16. [Cadastros Auxiliares](#16-cadastros-auxiliares)
17. [Usuários e Perfis](#17-usuários-e-perfis)
18. [Configurações](#18-configurações)
19. [Arquitetura — Camadas](#19-arquitetura--camadas)
20. [Modelo de Dados](#20-modelo-de-dados)
21. [Regras Gerais de Negócio](#21-regras-gerais-de-negócio)
22. [Rotas do Sistema](#22-rotas-do-sistema)
23. [Testes Automatizados](#23-testes-automatizados)
24. [Estrutura de Diretórios](#24-estrutura-de-diretórios)

---

## 1. Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 (PHP ^8.2) |
| Banco de dados | MySQL 8 (SQLite in-memory nos testes) |
| Front-end | Blade, Tailwind CSS 4, Vite 6, JavaScript vanilla |
| Gráficos | Chart.js |
| Códigos de barras | picqer/php-barcode-generator (servidor) e JsBarcode (navegador) |
| Planilhas | Maatwebsite/Excel (exportação da lista de compra) |
| Permissões | Spatie Laravel Permission (perfis + permissões granulares) |
| UI extras | Bladewind UI (checkbox e datepicker, com tradução pt_BR) |
| Cache/Sessão/Filas | Redis e/ou database |
| Infra | Docker (app PHP 8.4-FPM, Nginx, MySQL 8, PHPMyAdmin, Redis) |

---

## 2. Instalação com Docker

Pré-requisitos: Docker e Docker Compose.

```bash
docker-compose up -d
cp .env.example .env
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app npm install && docker compose exec app npm run build
```

Serviços expostos:

| Serviço | Porta |
|---|---|
| Aplicação (Nginx) | http://localhost:8000 |
| MySQL | 3300 |
| PHPMyAdmin | 8080 |

Sem Docker: siga o padrão Laravel (`composer install`, `php artisan key:generate`, configure o `.env`, `php artisan migrate --seed`, `php artisan serve`).

### Testes

```bash
php artisan test
```

Roda em SQLite in-memory. O CI (GitHub Actions) executa os testes em PHP 8.2, 8.3 e 8.4.

### Código de estilo

```bash
vendor/bin/pint
```

---

## 3. Acesso Inicial (Seed)

O seeder padrão (`php artisan db::seed`) cria:

- **Usuário administrador:** `admin@example.com` / senha `password` (`is_admin = true`)
- **8 unidades:** un, kg, g, l, ml, m, cx, pct
- **15 centros de custo** de instituição de ensino (cursos, laboratórios, setores e administrativo)
- **8 funcionários** fictícios
- **5 categorias** coloridas e **20 produtos de demonstração** (com estoques variados, incluindo abaixo do mínimo, para demonstrar os alertas)

> Altere a senha do usuário administrador após o primeiro acesso.

---

## 4. Autenticação e Permissões

- Login por **e-mail + senha** (`App\Http\Requests\LoginRequest`).
- Ao autenticar, a sessão é regenerada e o usuário é redirecionado ao dashboard.
- Logout invalida sessão e token.
- `/` redireciona para o dashboard (`/admin`).

### Modelo de acesso

| Nível | Como funciona |
|---|---|
| **Administrador** | `is_admin = true` — `Gate::before` no `AppServiceProvider` concede acesso total a todas as rotas |
| **Operador / demais perfis** | `is_admin = false` — acesso controlado por **perfis (roles)** do Spatie Permission, com permissões granulares por módulo e ação (`produtos.view`, `saida.create`, etc.) |

- O middleware **`CheckPermission`** (alias `permission`) é aplicado a **todas** as rotas `/admin/*`.
- As permissões exigidas por rota ficam centralizadas em **`App\Support\PermissionMap::ROUTE_PERMISSIONS`** (nome da rota → permissão).
- Rotas ausentes do mapa não exigem permissão (dashboard, login, logout).
- Usuários sem a permissão necessária recebem a página **403 — "Acesso negado"**.
- Os perfis são gerenciados em `/admin/perfis`, onde cada módulo (`PermissionMap::MODULES`) expõe as ações: visualizar, criar, editar, excluir, receber, gerenciar.

---

## 5. Dashboard

Disponível para **todos os usuários autenticados** (`/admin`). Exibe:

- **Total de produtos** cadastrados
- **Soma total do estoque**
- **Produtos com estoque baixo/crítico** (`stock <= min_stock`), com botão **"Baixar Lista de Compra"**
- **Produtos próximos ao mínimo**: `min_stock > 0` e `stock <= min_stock * (1 + margem/100)` — margem da configuração `alerta_estoque_acima_minimo` (padrão: 30%)
- **Saídas do dia**
- **8 últimas movimentações**

---

## 6. Produtos

CRUD completo (`/admin/produtos`), validado pelo FormRequest `ProductRequest` / `ProductBulkDestroyRequest`.

### Listagem

- **Busca** por nome **ou** código de barras
- **Filtro por status:** ativo / inativo
- **Filtro por categoria**
- **Paginação** (10 por página, preservando os filtros na query string)

### Cadastro e Edição

Campos: nome, código de barras, imagem, categoria, estoque atual, estoque mínimo, estoque máximo, unidade, observação e status ativo/inativo.

- O **código de barras** é gerado automaticamente se não informado.
- Ao **editar**, o **estoque atual é somente leitura** — o estoque só muda por Entrada, Saída ou Inventário (rastreabilidade total).
- **Imagem:** JPEG, PNG, JPG, WEBP ou GIF, até **2 MB**; armazenada em `storage/app/public/produtos`; ao trocar/remover, o arquivo antigo é apagado.

### Exclusão

- **Individual:** remove a imagem e aplica **soft delete**.
- **Em massa** (`/admin/produtos/bulk-destroy`): soft delete em todos os itens selecionados.

### Validações (`ProductRequest`)

| Campo | Regra |
|---|---|
| name | obrigatório, máx. 255 |
| barcode | opcional, máx. 44, **único** (ignora o próprio registro) |
| image | opcional, imagem, mimes jpeg/png/jpg/webp/gif, máx. 2048 KB |
| category_id | deve existir em `categories` |
| stock / min_stock / max_stock | inteiros >= 0 |
| note | opcional, máx. 500 |
| unit | máx. 10 (padrão via `prepareForValidation`: `un`) |
| is_active | booleano (normalizado no `prepareForValidation`) |

---

## 7. Código de Barras

- Barcode gerado com **12 dígitos aleatórios + dígito verificador EAN-13**, com unicidade garantida.
- Ao **excluir (soft delete)** o produto, o barcode é **anulado** para liberar o código.
- Ao **restaurar**, se o barcode for nulo/em uso, um **novo barcode único** é gerado.
- Exibição em **PNG EAN-13** no servidor (`/admin/produtos/{id}/barcode`), com cache de 24h.
- Nas etiquetas, o código é renderizado no navegador via **JsBarcode (CODE128)**.

---

## 8. Movimentações — Saída

Rotas: `/admin/saida` — núcleo operacional do sistema.

### Página de saída (fluxo em lote)

- Busca de produtos por nome (`product-name-search` + searchable-select).
- Preview via URL: `/admin/saida?barcode=XXX` mostra o produto (com imagem, estoque atual e mínimo); código inexistente exibe **"Produto não encontrado"**.
- Adicione dinamicamente vários produtos à lista, editando quantidades limitadas ao estoque de cada item.
- **Centro de Custo** e **Funcionário Responsável** são **obrigatórios** (`SaidaBatchRequest`).
- Toda a operação roda em **transação de banco**: itens válidos são processados; inválidos (produto inexistente, estoque insuficiente) acumulam erros detalhados.
- Um movimento `out` é criado **por item**, vinculados ao funcionário, centro de custo e usuário autenticado.

### Saída individual (`POST /admin/saida` — `saida.register`)

Validada por `SaidaRegisterRequest` (`barcode`, `quantity`, `cost_center_id` e `employee_id` obrigatórios). A **saída nunca pode ser maior que o estoque disponível**.

### Busca JSON

`GET /admin/saida/search?q=` retorna até 10 produtos (nome ou barcode) em JSON, exigindo mínimo de 2 caracteres.

---

## 9. Movimentações — Entrada

Rota: `/admin/entrada` (`EntradaRegisterRequest`).

1. Escaneie/informe o código de barras — o produto é pré-carregado com preview (imagem, estoque, novo estoque).
2. Informe a **quantidade** (inteiro >= 1).
3. Centro de custo é **opcional**.

A quantidade é **somada** ao estoque e um movimento `in` é gravado com o snapshot antes/depois.

---

## 10. Inventário (Ajuste de Estoque)

Rota: `/admin/inventario` (`InventarioAdjustRequest`).

1. Selecione o produto (por barcode ou listagem).
2. Informe o **ajuste**: inteiro **positivo** (sobra) ou **negativo** (quebra/falta).

**Regras:**

- O **estoque final não pode ficar negativo**.
- Ajuste zero **não gera movimento**.
- Movimento `adjust` grava `quantity` absoluta, reaproveita o **último centro de custo do produto** e rotula a nota como "Sobra em inventário" / "Quebra/Falta em inventário".

---

## 11. Status de Estoque

Badge calculado por `Product::stockStatus()`:

| Status | Regra |
|---|---|
| **Zerado** | `stock == 0` |
| **Crítico** | `stock <= min_stock` |
| **Baixo** | `stock <= min_stock * (1 + margem%/100)` |
| **Excesso** | `max_stock > 0` e `stock > max_stock` |
| **Normal** | demais casos |

---

## 12. Lista de Compra (Reposição)

- Lista os produtos com **`stock <= min_stock`**.
- **Quantidade a Pedir:** `max(0, max_stock - stock)` (zero se `max_stock <= 0`).
- **Exportação Excel (.xlsx)** em `/admin/lista-compra/export` (Maatwebsite/Excel).
- Itens já cobertos por solicitações pendentes/parciais são sinalizados.

---

## 13. Etiquetas

Rota: `/admin/etiquetas`.

- **Busca** por nome ou código; **ordenação** por nome, barcode ou estoque.
- **Seleção múltipla** e página de impressão (`/admin/etiquetas/imprimir?ids[]=`).
- Grade de 3 colunas, etiquetas de **34mm**: nome, **SVG do código de barras (CODE128 via JsBarcode)** e código numérico, com `window.print()` automático.

---

## 14. Histórico de Movimentações

Rota: `/admin/movimentacoes` — filtros por **tipo**, **funcionário**, **centro de custo** e **nome do produto**; paginação de 12 em 12.

---

## 15. Relatórios

Rota: `/admin/relatorios` — visão analítica por **período** (`from`/`to` em `dd-mm-aaaa`):

- Totais de entradas, saídas, ajustes e movimentações
- **Top 8 produtos mais retirados** (gráfico Chart.js)
- Produtos **críticos** e **aproximando do mínimo**
- **Atividade por funcionário** (entradas, saídas, operações)

---

## 16. Cadastros Auxiliares

CRUDs completos com validações em FormRequests, busca, paginação e regras de exclusão:

| Módulo | Rota | Validações principais | Exclusão |
|---|---|---|---|
| **Categorias** | `/admin/categorias` | nome obrigatório e único | soft delete (`CategoryRequest`) |
| **Unidades** | `/admin/unidades` | nome + abreviação (máx. 10, única) | soft delete (`UnitRequest`) |
| **Centros de Custo** | `/admin/centros-custo` | nome; tipo ∈ curso/setor/laboratorio/administrativo; código/cor opcionais | soft delete (`CostCenterRequest`) |
| **Funcionários** | `/admin/funcionarios` | nome obrigatório e único | exclusão definitiva (`EmployeeRequest`) |

---

## 17. Usuários e Perfis

### Usuários (`/admin/usuarios` — `UsuarioRequest`)

- Nome, e-mail único, senha com confirmação (**obrigatória ao criar**, opcional na edição — vazia mantém a atual, lógica no `validated()` do FormRequest).
- Perfis (roles) atribuídos via `syncRoles`.
- **Usuário não pode excluir o próprio cadastro** (`UsuarioService::destroy`).
- Soft delete.

### Perfis e permissões (`/admin/perfis` — `RoleRequest`)

- CRUD de perfis com seleção de permissões por módulo/ação (`PermissionMap`).
- O perfil built-in **Administrador** não pode ser excluído.
- Permissões sincronizadas no banco via `PermissionMap::syncPermissions()`.

---

## 18. Configurações

Rota: `/admin/configuracoes` (`ConfiguracoesSaveRequest`) — tabela `settings` (key-value). A página possui duas abas:

### Aba Geral

| Chave | Descrição | Padrão |
|---|---|---|
| `empresa_nome` | Nome da instituição | Minha Empresa Ltda |
| `empresa_cnpj` | CNPJ | — |
| `empresa_endereco` | Endereço | — |
| `empresa_telefone` | Telefone | — |
| `estoque_minimo_padrao` | Estoque mínimo sugerido | — |
| `alerta_estoque_acima_minimo` | Margem % p/ alerta de estoque próximo do mínimo (0–1000) | 30 |

### Aba Registros de Atividade

Rota: `/admin/configuracoes/registros` — histórico de criações, alterações, exclusões e restaurações (pacote `spatie/laravel-activitylog`, log `estoque`).

- Trait `App\Models\Concerns\LogsActivity` aplicado em: `Product`, `Category`, `Unit`, `CostCenter`, `Employee`, `PurchaseRequest`, `PurchaseRequestItem`, `User` e `Setting`.
- Registra o responsável (`causer`), o registro afetado (`subject`) e apenas os atributos alterados (dirty). `password` e `remember_token` nunca são logados (`config/activitylog.php`).
- Filtros por descrição/usuário, ação (Criação/Alteração/Exclusão) e módulo.
- Labels em pt-BR centralizados em `App\Support\ActivityLogMap`.
- Protegida pela mesma permissão `configuracoes.manage`.

---

## 19. Arquitetura — Camadas

O projeto segue responsabilidades claras:

```
Rota → Middleware (auth + permission) → FormRequest (validação) → Controller (orquestração) → Service (regras de negócio) → Model
```

- **`app/Http/Requests/`** — todos os formulários possuem FormRequest dedicado (`rules()`, `messages()`, `prepareForValidation()` para normalizações como JSON de itens em lote, defaults de `unit`/`is_active` e regras condicionais de senha). **Nenhuma validação fica dentro dos controllers.**
- **`app/Http/Controllers/`** — apenas orquestração: recebe dados já validados (`$request->validated()`), chama services e retorna views/redirects.
- **`app/Services/`** — regras de negócio: `StockService` (entrada/saída/lote/ajuste), `ProductService` (imagem + barcode), `UsuarioService`, `RoleService`, `PurchaseRequestService`.
- **`app/Support/PermissionMap`** — mapa central de módulos, ações e exigência por rota.
- **`app/Support/ActivityLogMap`** — labels pt-BR e helpers do histórico de atividade.
- **`app/Models/Concerns/LogsActivity`** — trait de log de atividade (criação/alteração/exclusão/restauração) aplicado nos models de negócio.
- **`app/Http/Middleware/CheckPermission`** — aplica o mapa de permissões (Spatie `can()`); administradores são liberados pelo `Gate::before`.

---

## 20. Modelo de Dados

| Tabela | Campos principais | Observações |
|---|---|---|
| `users` | name, email (único), password, **is_admin**, deleted_at | Soft deletes |
| `products` | name, barcode (único), image, category_id, stock, min_stock, max_stock, note, unit, is_active, deleted_at | Soft deletes; FK categoria → nullOnDelete |
| `movements` | product_id, cost_center_id, employee_id, **type (in/out/adjust)**, quantity, stock_before, stock_after, user_id, note | FK produto → cascade; demais nullOnDelete |
| `categories` | name (único), deleted_at | Soft deletes |
| `cost_centers` | name (único), type (curso/setor/laboratorio/administrativo), code, color, is_active, deleted_at | Soft deletes |
| `units` | name, abbreviation (único), deleted_at | Soft deletes |
| `employees` | name | **Sem** soft deletes |
| `settings` | key (único), value | Key-value store |
| `purchase_requests` (+ items) | status (pending/partial/delivered), note, quantities recebidas | Solicitações de compra |
| `activity_log` | log_name, description, event, subject/causer (morph), attribute_changes | `spatie/laravel-activitylog` — histórico de alterações |
| Tabelas Spatie | roles, permissions, model_has_*, permission_has_* | Perfis e permissões |

---

## 21. Regras Gerais de Negócio

1. **Rastreabilidade total do estoque:** nenhuma alteração direta no produto; tudo passa por um movimento (`in`, `out` ou `adjust`) com snapshot antes/depois e responsável.
2. **Saída nunca excede o estoque disponível** (individual e em lote).
3. **Inventário nunca deixa o estoque negativo**; ajuste zero não gera movimento.
4. **Barcode exclusivo** (EAN-13), liberado no soft delete e regerado na restauração se necessário.
5. **Permissões granulares** — administrador (`is_admin`) tem acesso total; demais usuários seguem perfis/roles (Spatie); sem permissão → página 403.
6. **Operações em lote são atômicas** (transação de banco).
7. **Exclusão "segura":** soft delete para produtos, categorias, unidades, centros de custo e usuários; imagens de produtos são removidas fisicamente.
8. **Usuário não se autoexclui.**
9. **Senhas:** mínimo 8 caracteres, com confirmação.

---

## 22. Rotas do Sistema

Base: rotas `/admin/*` exigem autenticação; a maioria exige permissão conforme `PermissionMap` (administradores são dispensados).

### Autenticação
| Método | URL | Rota |
|---|---|---|
| GET | `/login` | `login` |
| POST | `/login` | `login.post` |
| POST | `/logout` | `logout` |

### Principal
| Método | URL | Rota |
|---|---|---|
| GET | `/admin` | `dashboard` |

### Produtos
| Método | URL | Rota |
|---|---|---|
| GET | `/admin/produtos` | `produtos.index` |
| GET | `/admin/produtos/novo` | `produtos.create` |
| POST | `/admin/produtos` | `produtos.store` |
| GET | `/admin/produtos/{id}/editar` | `produtos.edit` |
| PUT | `/admin/produtos/{id}` | `produtos.update` |
| DELETE | `/admin/produtos/{id}` | `produtos.destroy` |
| POST | `/admin/produtos/bulk-destroy` | `produtos.bulkDestroy` |
| GET | `/admin/produtos/{id}/barcode` | `produtos.barcode` (PNG) |

### Saída / Entrada / Inventário
| Método | URL | Rota |
|---|---|---|
| GET | `/admin/saida` | `saida` (suporta `?barcode=`) |
| POST | `/admin/saida` | `saida.register` |
| POST | `/admin/saida/batch` | `saida.batch` |
| GET | `/admin/saida/search` | `saida.search` (JSON) |
| GET/POST | `/admin/entrada` | `entrada` / `entrada.register` |
| GET/POST | `/admin/inventario` | `inventario` / `inventario.register` |

### Consultas
| Método | URL | Rota |
|---|---|---|
| GET | `/admin/movimentacoes` | `movimentacoes` |
| GET | `/admin/relatorios` | `relatorios` |
| GET | `/admin/lista-compra` | `lista-compra.index` |
| GET | `/admin/lista-compra/export` | `lista-compra.export` (xlsx) |

### Solicitações de compra
| Método | URL | Rota |
|---|---|---|
| GET/POST | `/admin/solicitacoes` | `solicitacoes.index` / `solicitacoes.store` |
| GET | `/admin/solicitacoes/{id}` | `solicitacoes.show` |
| POST | `/admin/solicitacoes/{id}/entregar` | `solicitacoes.deliver` |
| DELETE | `/admin/solicitacoes/{id}` | `solicitacoes.destroy` |

### Cadastros e administração
| Método | URL | Rotas |
|---|---|---|
| CRUD | `/admin/categorias` | `categorias.*` |
| CRUD | `/admin/unidades` | `unidades.*` |
| CRUD | `/admin/centros-custo` | `centros-custo.*` |
| CRUD | `/admin/funcionarios` | `funcionarios.*` |
| CRUD | `/admin/usuarios` | `usuarios.*` |
| CRUD | `/admin/perfis` | `roles.*` |
| GET/POST | `/admin/configuracoes` | `configuracoes` / `configuracoes.save` |
| GET | `/admin/configuracoes/registros` | `configuracoes.registros` |
| GET | `/admin/etiquetas` | `etiquetas.index` |
| GET | `/admin/etiquetas/imprimir` | `etiquetas.print` |

> Health check: `GET /up`.

---

## 23. Testes Automatizados

PHPUnit, SQLite in-memory, **96 testes / 346 asserções**:

| Arquivo | Cobertura |
|---|---|
| **`FullApplicationTest`** | **Teste geral: login/logout, dashboard, CRUD completo de produtos, categorias, unidades, funcionários, centros de custo e usuários (com roles e autoexclusão), entrada, saída em lote (sucesso/vazio/estoque insuficiente), busca JSON, inventário (ajuste e limite zero), movimentações, relatórios, lista de compra (índice + export Excel), etiquetas (índice + impressão) e configurações** |
| `AdminAuthorizationTest` | Bloqueio de não-administradores fora do dashboard |
| `SaidaFlowTest` | Fluxo de saída: preview por barcode, "produto não encontrado", registro individual e vínculo com funcionário/centro de custo |
| `CostCenterFlowTest` | Exigência de centro de custo na saída |
| `ProductImageTest` | Upload de imagem de produto e preview na saída |
| `SoftDeleteTest` | Soft delete + comportamento do barcode (liberação/regeneração) |
| `StockMovementTest` | Status de estoque |
| `SearchableSelectTest` | Componente de select com busca |
| `PurchaseRequestFlowTest` | Solicitações de compra: geração, entrega total/parcial, exclusão |
| `RolesFlowTest` | Perfis e permissões granulares, bypass de admin, CRUD de perfis |
| `ActivityLogFlowTest` | Log de atividade: criação/alteração/exclusão com responsável, não-log de senha, filtros da aba Registros e permissão |

CI: GitHub Actions (`.github/workflows/tests.yml`) em PHP 8.2 / 8.3 / 8.4.

---

## 24. Estrutura de Direatórios

```
app-laravel/
├── app/
│   ├── Exceptions/DomainException.php
│   ├── Exports/ListaCompraExport.php
│   ├── Http/
│   │   ├── Controllers/        (auth + 17 controllers)
│   │   ├── Middleware/CheckPermission.php
│   │   └── Requests/           (16 FormRequests — validação centralizada)
│   ├── Models/                 (User, Product, Movement, Category, Unit,
│   │   │                        CostCenter, Employee, Setting, PurchaseRequest…)
│   │   └── Concerns/LogsActivity.php
│   ├── Services/               (StockService, ProductService, UsuarioService,
│   │                            RoleService, PurchaseRequestService)
│   └── Support/                (PermissionMap.php, ActivityLogMap.php)
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/                (DatabaseSeeder, UnitSeeder, CostCenterSeeder,
│                                EmployeeSeeder, ProdutosDemoSeeder)
├── resources/views/            (auth, dashboard, produtos, entrada, saida,
│                                inventario, movimentacoes, relatorios,
│                                etiquetas, lista-compra, solicitacoes,
│                                categorias, unidades, centros-custo,
│                                funcionarios, usuarios, configuracoes, errors)
│       └── components/app/     (layout, card, btn, searchable-select,
│                                product-name-search, image-preview, stock-badge…)
├── routes/web.php
├── tests/Feature/              (11 arquivos, 96 testes)
├── docker/, docker-compose.yml, Dockerfile
└── .github/workflows/          (tests.yml)
```

---

*Documentação gerada com base na análise completa do código-fonte e na suíte de testes do sistema.*
