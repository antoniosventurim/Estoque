# Meu Estoque — São Mateus

Sistema web de **controle de estoque** desenvolvido em **Laravel 12** (PHP ^8.2), com interface em Blade + Tailwind CSS 4, voltado para uma instituição de ensino (cursos, laboratórios, setores e administração).

O sistema gerencia produtos com código de barras, movimentações de entrada/saída/inventário, centros de custo, funcionários, etiquetas impressas, relatórios e lista de compra (reposição).

---

## Índice

1. [Stack Tecnológica](#1-stack-tecnológica)
2. [Instalação com Docker](#2-instalação-com-docker)
3. [Acesso Inicial (Seed)](#3-acesso-inicial-seed)
4. [Autenticação e Perfis de Usuário](#4-autenticação-e-perfis-de-usuário)
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
16. [Cadastro de Categorias](#16-cadastro-de-categorias)
17. [Cadastro de Unidades](#17-cadastro-de-unidades)
18. [Centros de Custo](#18-centros-de-custo)
19. [Funcionários](#19-funcionários)
20. [Usuários](#20-usuários)
21. [Configurações](#21-configurações)
22. [Modelo de Dados](#22-modelo-de-dados)
23. [Regras Gerais de Negócio](#23-regras-gerais-de-negócio)
24. [Rotas do Sistema](#24-rotas-do-sistema)
25. [Testes Automatizados](#25-testes-automatizados)
26. [Estrutura de Diretórios](#26-estrutura-de-diretórios)

---

## 1. Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | Laravel 12 (PHP ^8.2) |
| Banco de dados | MySQL 8 (SQLite para testes) |
| Front-end | Blade, Tailwind CSS 4, Vite 6, JavaScript vanilla |
| Gráficos | Chart.js |
| Códigos de barras | picqer/php-barcode-generator (servidor) e JsBarcode (navegador) |
| Planilhas | Maatwebsite/Excel (exportação da lista de compra) |
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
# (opcional, se usar SQLite) docker compose exec app touch database/database.sqlite
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

Roda em SQLite in-memory. CI no GitHub Actions executa os testes em PHP 8.2, 8.3 e 8.4.

---

## 3. Acesso Inicial (Seed)

O seeder padrão (`php artisan db::seed`) cria:

- **Usuário administrador:** `admin@example.com` / senha `password` (`is_admin = true`)
- **8 unidades:** un, kg, g, l, ml, m, cx, pct
- **15 centros de custo** de instituição de ensino:
  - Cursos: Medicina, Enfermagem, Odontologia, Fisioterapia, Biomedicina, Farmácia, Nutrição, Administração
  - Laboratórios: Laboratório de Anatomia, Laboratório de Enfermagem, Clínica de Odontologia
  - Setores: Biblioteca, Coordenação Acadêmica, Financeiro, TI
  - Administrativo
- **8 funcionários** fictícios
- **5 categorias** coloridas e **20 produtos de demonstração** (com estoques variados, incluindo zerados e abaixo do mínimo, para demonstrar os alertas)

> Altere a senha do usuário administrador após o primeiro acesso.

---

## 4. Autenticação e Perfis de Usuário

- Login por **e-mail + senha** (guard `web`, sessão em banco de dados).
- Ao autenticar, a sessão é regenerada e o usuário é redirecionado ao dashboard.
- Logout invalida sessão e token.
- `/` redireciona para o dashboard (`/admin`).

### Perfis

| Perfil | Flag | Permissões |
|---|---|---|
| **Administrador** | `is_admin = true` | Acesso completo a todas as telas de `/admin/*` |
| **Operador** | `is_admin = false` | Acesso apenas ao **Dashboard** |

O middleware `AdminMiddleware` (alias `admin`) protege **todas** as rotas `/admin/*` exceto o dashboard. Usuários não-administradores que tentem acessar outras telas são redirecionados ao dashboard com a mensagem "Acesso restrito.".

---

## 5. Dashboard

Disponível para **todos os usuários autenticados** (`/admin`). Exibe:

- **Total de produtos** cadastrados
- **Soma total do estoque** (unidades em estoque)
- **Produtos com estoque baixo/crítico** (`stock <= min_stock`), com botão **"Baixar Lista de Compra"** (exportação Excel)
- **Produtos próximos ao mínimo**: `min_stock > 0` e `stock <= min_stock * (1 + margem/100)`, onde a margem percentual vem da configuração `alerta_estoque_acima_minimo` (padrão: 30%)
- **Saídas do dia**: soma das quantidades dos movimentos de saída do dia corrente
- **8 últimas movimentações** realizadas

---

## 6. Produtos

CRUD completo (`/admin/produtos`), restrito a administradores.

### Listagem

- **Busca** por nome **ou** código de barras ( LIKE )
- **Filtro por status:** ativo / inativo
- **Filtro por categoria**
- **Ordenação** e **paginação** (10 por página, preservando os filtros na query string)

### Cadastro e Edição

Campos: nome, código de barras, imagem, categoria, estoque atual, estoque mínimo, estoque máximo, unidade, observação e status ativo/inativo.

- O **código de barras** é gerado automaticamente se não informado (ver seção [Código de Barras](#7-código-de-barras)).
- Ao **editar**, o campo **estoque atual é somente leitura** — o estoque só pode ser alterado por Entrada, Saída ou Inventário (garante rastreabilidade total).
- **Imagem:** formatos JPEG, PNG, JPG, WEBP ou GIF, até **2 MB**; armazenada em `storage/app/public/produtos`; ao trocar ou remover imagem, o arquivo antigo é apagado do disco.

### Exclusão

- **Exclusão individual:** remove a imagem do disco e aplica **soft delete** (o registro é preservado, não é apagado fisicamente).
- **Exclusão em massa:** seleção múltiplica via checkboxes + botão "Excluir selecionados"; apaga imagens e aplica soft delete em todos os itens selecionados.

### Validações

| Campo | Regra |
|---|---|
| name | obrigatório, máx. 255 caracteres |
| barcode | opcional, máx. 44 caracteres, **único** (ignora o próprio registro) |
| image | opcional, imagem, mimes jpeg/png/jpg/webp/gif, máx. 2048 KB |
| category_id | deve existir na tabela de categorias |
| stock / min_stock / max_stock | inteiros >= 0 |
| note | opcional, máx. 500 caracteres |
| unit | máx. 10 caracteres (padrão: `un`) |
| is_active | booleano |

---

## 7. Código de Barras

Regra sofisticada de geração e reutilização:

- Barcode gerado automaticamente com **12 dígitos aleatórios + dígito verificador EAN-13**, com garantia de unicidade no banco.
- Ao **excluir (soft delete)** o produto, o barcode é **anulado** (`null`) para liberar o código a outro produto.
- Ao **restaurar** o produto, se o barcode for nulo ou já estiver em uso, um **novo barcode único** é gerado automaticamente.
- Exibição em **PNG EAN-13** gerado no servidor (`/admin/produtos/{id}/barcode`), com cache de 24h (largura 2, altura 60).
- Nas etiquetas, o código é renderizado no navegador via **JsBarcode (CODE128)**.

---

## 8. Movimentações — Saída

Rotas: `/admin/saida` — núcleo operacional do sistema, com leitura por scanner de código de barras.

### Saída individual

1. Informe/escaneie o **código de barras** do produto.
2. Informe a **quantidade** (inteiro >= 1).
3. Selecione o **Centro de Custo** (**obrigatório**) e o **Funcionário** (**obrigatório**).

**Regras:**

- A **saída nunca pode ser maior que o estoque disponível** (validado no servidor e no cliente) — erro: "Quantidade solicitada maior que o estoque disponível".
- O produto deve existir e estar ativo.
- O estoque é atualizado e um movimento `out` é gravado com **estoque antes/depois**, **usuário responsável**, **funcionário** e **centro de custo**.
- Mensagem de sucesso com countdown de 20 segundos.

### Saída em lote (vários itens)

- Adicione dinamicamente vários produtos (barcode + quantidade), editando as quantidades limitadas ao estoque de cada item.
- Centros de custo e funcionário são **obrigatórios** para a operação inteira.
- Toda a operação é executada em **transação de banco** (`DB::transaction`): ou todos os itens são processados, ou nenhum.
- Itens inválidos (produto inexistente, estoque insuficiente) acumulam erros detalhados.
- Um movimento `out` é criado **por item**, todos vinculados ao mesmo funcionário e centro de custo.

---

## 9. Movimentações — Entrada

Rota: `/admin/entrada`

1. Escaneie/informe o **código de barras** — o sistema pré-carrega o produto com preview do estoque atual, mínimo, máximo e **cálculo do novo estoque**.
2. Informe a **quantidade** (inteiro >= 1).
3. Centro de custo é **opcional**.

**Regras:**

- O produto deve existir.
- A quantidade é **somada** ao estoque atual.
- Um movimento `in` é gravado com o **usuário responsável** e o snapshot de estoque antes/depois.

---

## 10. Inventário (Ajuste de Estoque)

Rota: `/admin/inventario` — conferência física do estoque.

1. A listagem mostra todos os produtos; selecione por **barcode** ou **produto**.
2. Informe o **ajuste**: número inteiro **positivo** (sobra) ou **negativo** (quebra/falta).

**Regras:**

- O **estoque final não pode ficar negativo** — o sistema valida e retorna erro com o estoque atual.
- Ajustes iguais a zero **não geram movimento**.
- Quando gera movimento (`adjust`):
  - `quantity` é gravada com o valor **absoluto** do ajuste;
  - o sistema reaproveita o **último centro de custo usado no produto** (se houver);
  - a observação é rotulada automaticamente como **"Sobra em inventário"** (ajuste positivo) ou **"Quebra/Falta em inventário"** (ajuste negativo).

---

## 11. Status de Estoque

Cada produto recebe um status exibido em badges em todo o sistema, calculado por `Product::stockStatus()`:

| Status | Regra |
|---|---|
| **Zerado** | `stock == 0` |
| **Crítico** | `stock <= min_stock` |
| **Baixo** | `stock <= min_stock * (1 + margem%/100)` — margem da configuração `alerta_estoque_acima_minimo` (padrão 30%) |
| **Excesso** | `max_stock > 0` e `stock > max_stock` |
| **Normal** | demais casos |

---

## 12. Lista de Compra (Reposição)

- Seleciona automaticamente os produtos com **`stock <= min_stock`**.
- Para cada item, calcula a **Quantidade a Pedir**: `max(0, max_stock - stock)` (zero se `max_stock <= 0`).
- **Exportação para Excel (.xlsx)** via Maatwebsite/Excel (`/admin/lista-compra/export`), com colunas: #, Produto, Categoria, Unidade, Estoque Atual, Mínimo, Máximo e Quantidade a Pedir.
- Também acessível pelo botão "Baixar Lista de Compra" no dashboard.

---

## 13. Etiquetas

Rota: `/admin/etiquetas` — geração e impressão de etiquetas com código de barras.

- **Busca** por nome ou código de produtos.
- **Ordenação** por nome, código de barras ou estoque (asc/desc).
- **Seleção múltipla** de produtos via checkboxes.
- A página de impressão (`/admin/etiquetas/imprimir`) monta uma **grade de 3 colunas** com etiquetas de **34mm**, contendo: nome do produto, **SVG do código de barras (CODE128 via JsBarcode)** e código numérico.
- A **impressão é disparada automaticamente** (`window.print()`) após a renderização.

---

## 14. Histórico de Movimentações

Rota: `/admin/movimentacoes` — consulta completa do histórico.

- Filtros: **tipo** (entrada / saída / ajuste), **funcionário**, **centro de custo** e **nome do produto**.
- Ordenação das mais recentes para as mais antigas; paginação de 12 em 12.
- Cada movimento registra: produto, tipo, quantidade, estoque antes/depois, usuário responsável, funcionário e centro de custo.

---

## 15. Relatórios

Rota: `/admin/relatorios` — visão analítica por **período** (data inicial e final).

**Métricas do período:**

- Total de **entradas** (soma de quantidades)
- Total de **saídas** (soma de quantidades)
- Número de **ajustes**
- **Total de movimentações**

**Análises:**

- **Top 8 produtos mais retirados** (com gráfico de barras horizontais — Chart.js)
- Produtos **críticos** (`stock <= min_stock`)
- Produtos **aproximando do mínimo** (`stock > min_stock && stock <= ceil(min_stock * 1.5)`)
- **Atividade por funcionário**: entradas, saídas e total de operações

---

## 16. Cadastro de Categorias

CRUD (`/admin/categorias`), restrito a administradores.

- Busca por nome; listagem com contagem de produtos vinculados (`products_count`).
- Validações: nome **obrigatório e único**; cor opcional (máx. 7 caracteres, formato hex).
- **Soft delete**; produtos vinculados a uma categoria excluída ficam sem categoria (`nullOnDelete`).

---

## 17. Cadastro de Unidades

CRUD (`/admin/unidades`), restrito a administradores.

- Validações: nome obrigatório; **abreviação obrigatória, máx. 10 caracteres e única**.
- Listagem com contagem de produtos que usam a unidade.
- **Soft delete**.

---

## 18. Centros de Custo

CRUD (`/admin/centros-custo`), restrito a administradores. Representam os destinos das saídas de estoque (cursos, setores, laboratórios, administração).

- Campos: nome, tipo, código, cor e status ativo/inativo.
- Validações: nome obrigatório e único; **tipo obrigatório**, sendo uma das opções: **curso, setor, laboratorio, administrativo**; código máx. 20; cor máx. 7 (hex); status booleano.
- Busca por nome/código e filtro por tipo.
- **Soft delete**.

---

## 19. Funcionários

CRUD (`/admin/funcionarios`), restrito a administradores. Registram quem retira o material nas saídas.

- Validações: nome obrigatório e **único**.
- Busca por nome.
- **Não possui soft delete** (exclusão definitiva). Vínculos em movimentos históricos são preservados.

---

## 20. Usuários

CRUD (`/admin/usuarios`), restrito a administradores.

- Validações:
  - nome obrigatório;
  - e-mail obrigatório e **único** (ignora o próprio registro na edição);
  - senha: **obrigatória + confirmação + mínimo de 8 caracteres ao criar**; na edição, se vazia mantém a atual;
  - perfil: administrador (`is_admin`) ou operador.
- **Regra:** o usuário **não pode excluir o próprio usuário** — erro: "Você não pode excluir o próprio usuário".
- **Soft delete**.

---

## 21. Configurações

Rota: `/admin/configuracoes` — parâmetros gerais do sistema, armazenados em tabela `settings` (key-value):

| Chave | Descrição | Padrão |
|---|---|---|
| `empresa_nome` | Nome da empresa/instituição | Minha Empresa Ltda |
| `empresa_cnpj` | CNPJ | — |
| `empresa_endereco` | Endereço | — |
| `empresa_telefone` | Telefone | — |
| `estoque_minimo_padrao` | Valor padrão sugerido para estoque mínimo de novos produtos | — |
| `alerta_estoque_acima_minimo` | Margem percentual (0–1000) usada no dashboard e nos badges para alertar estoque "próximo do mínimo" | 30 |

---

## 22. Modelo de Dados

| Tabela | Campos principais | Observações |
|---|---|---|
| `users` | name, email (único), password, **is_admin**, deleted_at | Soft deletes |
| `products` | name, barcode (único), image, category_id, stock, min_stock, max_stock, note, unit, is_active, deleted_at | Soft deletes; FK categoria → nullOnDelete |
| `movements` | product_id, cost_center_id, employee_id, **type (enum: in/out/adjust)**, quantity, stock_before, stock_after, user_id, document, note | FK produto → cascadeOnDelete; demais FKs nullOnDelete |
| `categories` | name (único), color, deleted_at | Soft deletes |
| `cost_centers` | name (único), type (curso/setor/laboratorio/administrativo), code, color, is_active, deleted_at | Soft deletes |
| `units` | name, abbreviation (único, varchar 10), deleted_at | Soft deletes |
| `employees` | name | **Sem** soft deletes |
| `settings` | key (único), value | Key-value store |

Relacionamentos principais:

- `Product` → `Category` (belongsTo), `Unit` (belongsTo via abreviação), `Movement` (hasMany)
- `Movement` → `Product`, `CostCenter`, `User`, `Employee` (belongsTo)
- `User` → `Movement` (hasMany); `CostCenter`/`Employee` → `Movement` (hasMany)

---

## 23. Regras Gerais de Negócio

1. **Rastreabilidade total do estoque:** nenhuma alteração de estoque é feita diretamente no produto; toda alteração passa por um movimento (`in`, `out` ou `adjust`) que grava snapshot de estoque antes/depois e o responsável.
2. **Saída nunca excede o estoque disponível** (individual e em lote).
3. **Inventário nunca deixa o estoque negativo**; ajuste zero não gera movimento.
4. **Barcode exclusivo**, gerado com dígito verificador EAN-13; liberado no soft delete e regerado na restauração se necessário.
5. **Operador não acessa a administração** — apenas o dashboard; todas as demais telas exigem administrador.
6. **Operações em lote são atômicas** (transação de banco).
7. **Exclusão "segura":** soft delete para produtos, categorias, unidades, centros de custo e usuários; imagens de produtos são removidas fisicamente.
8. **Usuário não se autoexclui.**
9. **Senhas:** mínimo 8 caracteres, com confirmação.

---

## 24. Rotas do Sistema

Base: todas as rotas `/admin/*` exigem autenticação; exceto o dashboard, exigem também `is_admin = true`.

### Autenticação
| Método | URL | Descrição |
|---|---|---|
| GET | `/login` | Tela de login |
| POST | `/login` | Autenticar |
| POST | `/logout` | Sair |

### Principal
| Método | URL | Descrição |
|---|---|---|
| GET | `/admin` | Dashboard |

### Produtos
| Método | URL | Descrição |
|---|---|---|
| GET | `/admin/produtos` | Listar |
| GET/POST | `/admin/produtos/novo` | Criar |
| GET/PUT | `/admin/produtos/{id}/editar` | Editar |
| DELETE | `/admin/produtos/{id}` | Excluir (soft) |
| POST | `/admin/produtos/bulk-destroy` | Exclusão em massa |
| GET | `/admin/produtos/{id}/barcode` | PNG do código de barras |

### Movimentações
| Método | URL | Descrição |
|---|---|---|
| GET/POST | `/admin/saida` | Saída individual |
| POST | `/admin/saida/batch` | Saída em lote |
| GET | `/admin/saida/search` | Busca de produto (JSON) |
| GET/POST | `/admin/entrada` | Entrada |
| GET/POST | `/admin/inventario` | Inventário / ajuste |

### Consultas e relatórios
| Método | URL | Descrição |
|---|---|---|
| GET | `/admin/movimentacoes` | Histórico |
| GET | `/admin/relatorios` | Relatórios por período |
| GET | `/admin/lista-compra/export` | Exportar lista de compra (Excel) |
| GET | `/admin/etiquetas` | Etiquetas — busca/seleção |
| GET | `/admin/etiquetas/imprimir` | Impressão de etiquetas |

### Cadastros auxiliares
| Método | URL | Descrição |
|---|---|---|
| CRUD | `/admin/categorias[/novo|/{id}/editar]` | Categorias |
| CRUD | `/admin/unidades[/novo|/{id}/editar]` | Unidades |
| CRUD | `/admin/centros-custo[/novo|/{id}/editar]` | Centros de custo |
| CRUD | `/admin/funcionarios[/novo|/{id}/editar]` | Funcionários |
| CRUD | `/admin/usuarios[/novo|/{id}/editar]` | Usuários |
| GET/POST | `/admin/configuracoes` | Configurações |

> Rota de health check: `GET /up`.

---

## 25. Testes Automatizados

PHPUnit 11, rodando em SQLite in-memory (`php artisan test`):

| Teste | Cobertura |
|---|---|
| `AdminAuthorizationTest` | Bloqueio de não-admins fora do dashboard |
| `SaidaFlowTest` | Fluxo de saída (individual e lote) |
| `CostCenterFlowTest` | Exigência de centro de custo na saída |
| `ProductImageTest` | Upload e remoção de imagens de produtos |
| `SoftDeleteTest` | Soft delete + comportamento do barcode (liberação/regeneração) |
| `StockMovementTest` | Status de estoque |
| `SearchableSelectTest` | Componente de select com busca |

CI: workflow GitHub Actions (`tests.yml`) executando os testes em PHP 8.2 / 8.3 / 8.4.

---

## 26. Estrutura de Diretórios

```
app-laravel/
├── app/
│   ├── Exports/ListaCompraExport.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── BarcodeController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ConfiguracoesController.php
│   │   │   ├── CostCenterController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── EmployeeController.php
│   │   │   ├── EtiquetaController.php
│   │   │   ├── InventarioController.php
│   │   │   ├── ListaCompraController.php
│   │   │   ├── MovimentacoesController.php
│   │   │   ├── MovimentoController.php
│   │   │   ├── ProductController.php
│   │   │   ├── RelatorioController.php
│   │   │   ├── UnitController.php
│   │   │   └── UsuarioController.php
│   │   └── Middleware/AdminMiddleware.php
│   └── Models/
│       ├── User.php, Product.php, Movement.php, Category.php
│       ├── CostCenter.php, Employee.php, Unit.php, Setting.php
├── database/
│   ├── migrations/   (tabelas do sistema)
│   └── seeders/      (DatabaseSeeder, CostCenterSeeder, EmployeeSeeder,
│                      ProdutosDemoSeeder, UnitSeeder)
├── resources/views/  (auth, dashboard, produtos, entrada, saida, inventario,
│                      movimentacoes, relatorios, etiquetas, lista-compra,
│                      categorias, unidades, centros-custo, funcionarios,
│                      usuarios, configuracoes + componentes Blade reutilizáveis)
├── routes/web.php
├── tests/Feature/
├── docker/, docker-compose.yml, Dockerfile
└── .github/workflows/
```

Componentes Blade reutilizáveis (`resources/views/components/app/`): layout com sidebar recolhível, page-header, card, botões, inputs, badges, **stock-badge**, flash messages, paginação, preview de imagem, busca por código de barras, busca por nome de produto e **searchable-select** (select com busca, navegação por teclado e eventos customizados).

---

*Documento gerado com base na análise completa do código-fonte do sistema.*
