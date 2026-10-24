# Lista de Compra — Adicionar Próximos ao Estoque Mínimo

> 🛒 **Funcionalidade:** a Lista de Compra agora permite incluir produtos **próximos ao estoque mínimo** (acima do mínimo, mas dentro da margem de alerta) além dos que já estão **abaixo do mínimo**.

---

## Visão geral

A Lista de Compra (`/admin/lista-compra`) reúne os produtos que precisam de reposição. Ela é composta por duas origens:

| Origem | Como entra | Status na tabela |
|---|---|---|
| **Abaixo do mínimo** | Automática (calculada por `stock <= min_stock`) | `—` ou `Aguardando recebimento` |
| **Próximos ao mínimo (manual)** | Botão **Adicionar Próximos** / link do Dashboard | `Adicionado manualmente` + botão de remover |

O critério de "próximo ao mínimo" usa a mesma regra do cartão do Dashboard:

```
min_stock > 0
stock > min_stock
stock * 100 <= min_stock * (100 + margem)
```

A **margem** vem do setting `alerta_estoque_acima_minimo` (padrão: **30%**).

---

## Como usar

1. Abra **Lista de Compra** no menu (ou clique em **Adicionar à Lista** no cartão "Próximos ao Estoque Mínimo" do Dashboard — o modal abre automaticamente via `?adicionar=1`).
2. Clique em **Adicionar Próximos** no cabeçalho da página.
3. No modal, selecione os produtos (checkbox individual ou "selecionar todos"). As colunas mostram **Estoque**, **Mínimo** e **Máximo**, e a de **A Pedir** já vem preenchida com a diferença até o estoque máximo (`máximo − atual`, piso 1) — ajuste se precisar.
4. Confirme em **Adicionar itens à lista de compra**.
5. Os itens passam a aparecer na tabela com o badge **Adicionado manualmente** e podem ser **removidos** pelo ícone de lixeira.
6. Com a lista pronta, use **Gerar Solicitação** — o modal também exibe **Estoque**, **Máximo** e **A Pedir** (quantidade definida ao adicionar, ou a que falta para o máximo); os itens manuais entram junto com os abaixo do mínimo.

### Regras de negócio

- **Não duplica:** produtos já abaixo do mínimo ou já presentes na lista são ignorados no servidor (mensagem "Nenhum item novo para adicionar").
- **Não inclui** produtos que já têm solicitação pendente/parcial ("Aguardando recebimento") no modal de adição.
- **Idempotente:** reenviar o mesmo formulário não cria registros duplicados (constraint `unique` em `product_id`).
- **Gerar Solicitação** consome os itens manuais: ao criar a solicitação, as entradas correspondentes em `purchase_list_items` são **removidas** (o status volta a ser controlado pela solicitação).
- **Exportar Excel** inclui os itens manuais junto com os automáticos.

---

## Rotas e permissões

| Rota | Método | Permissão |
|---|---|---|
| `/admin/lista-compra` | GET | `lista-compra.view` |
| `/admin/lista-compra/export` | GET | `lista-compra.view` |
| `/admin/lista-compra/adicionar` | POST | `lista-compra.create` |
| `/admin/lista-compra/itens/{id}` | DELETE | `lista-compra.delete` |

As ações `create` e `delete` foram adicionadas ao módulo `lista-compra` em `app/Support/PermissionMap.php` e já entram no papel **Operador** (`OPERATOR_PERMISSIONS`).

### Instalação existente

Para registrar as novas permissões em um banco já em uso:

```bash
docker compose exec -T app php artisan db:seed --class=PermissionSeeder --force
```

> Em instalações novas, `php artisan migrate --seed` cuida de tudo (a tabela `purchase_list_items` é criada pela migration `2026_10_23_200000_create_purchase_list_items_table`).

---

## Modelo de dados

```
purchase_list_items
├── id
├── product_id → products (FK, cascade on delete, unique)
├── quantity   (quantidade a pedir escolhida pelo usuário)
├── user_id    → users (FK, nullable, null on delete)
└── timestamps
```

- Model: `app/Models/PurchaseListItem` (com `LogsActivity`).
- Validação: `app/Http/Requests/PurchaseListAddRequest` (`product_ids` obrigatório, existente e não excluído; `quantities` ≥ 1).
- O produto excluído (**soft delete**) mantém o vínculo; se restaurado, o item reaparece na lista.

---

## Fluxo com Solicitações de Compra

```
Próximos ao mínimo ──(Adicionar)──▶ Lista de Compra ──(Gerar Solicitação)──▶ Solicitação pendente
                                                                   │
                                                                   ▼
                                                        purchase_list_items removidos
                                                                   │
                                                                   ▼
                                                        Recebimento (entrada) atualiza estoque
```

A geração (`PurchaseRequestService::generate`) agora inclui:

1. Produtos com `stock <= min_stock` (comportamento original);
2. Produtos com entrada em `purchase_list_items`, mesmo estando acima do mínimo — usando a **quantidade armazenada** como padrão (o campo no modal de geração já vem preenchido com ela).

---

## Arquivos alterados

| Arquivo | Mudança |
|---|---|
| `database/migrations/2026_10_23_200000_create_purchase_list_items_table.php` | Nova tabela |
| `app/Models/PurchaseListItem.php` | Novo model |
| `app/Http/Requests/PurchaseListAddRequest.php` | Validação do POST |
| `app/Http/Controllers/ListaCompraController.php` | `index` unificado, `addItems`, `destroyItem` |
| `app/Exports/ListaCompraExport.php` | Inclui itens manuais |
| `app/Services/PurchaseRequestService.php` | Geração inclui itens manuais e os consome |
| `app/Support/PermissionMap.php` | Ações `create`/`delete` no módulo |
| `routes/web.php` | POST/DELETE novos |
| `resources/views/lista-compra.blade.php` | Modal de adição (colunas Estoque/Mínimo/Máximo e "A Pedir" = diferença), badges, remover, modal de geração com coluna Máximo e prefill |
| `resources/views/dashboard.blade.php` | Link **Adicionar à Lista** no cartão de próximos |
| `tests/Feature/PurchaseListAddTest.php` | Cobertura da funcionalidade |

---

## Testes

```bash
docker compose exec -T app vendor/bin/phpunit --filter PurchaseListAddTest
```

Cenários cobertos: listagem no modal, criação de itens, skip de abaixo-do-mínimo/duplicados, validação de quantidades, remoção, consumo na geração de solicitação e permissões de operador (`view` vs `create`/`delete`).

---

## Notas técnicas

- A query de alerta usa aritmética inteira (`stock * 100 <= min_stock * (100 + margem)`) para ser portável entre MySQL (produção) e SQLite (testes) — o `/` inteiro do SQLite quebraria a fórmula fracionada.
- Ambos os modais de adição e de geração exibem as colunas **Estoque**, **Mínimo**/**Máximo** e **A Pedir**. Nos dois, o "A Pedir" vem preenchido com a diferença até o estoque máximo (`máximo − atual`, piso 1) — no modal de geração, itens vindos da lista manual usam a quantidade definida ao adicionar.
- O modal de adição desabilita os campos de quantidade de itens não marcados antes do submit, evitando erro de validação por campos vazios.
- `?adicionar=1` na URL abre o modal automaticamente (usado pelo link do Dashboard).
