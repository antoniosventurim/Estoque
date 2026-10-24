<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseListItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Support\PermissionMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PurchaseListAddTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        PermissionMap::syncPermissions();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function nearMinProduct(array $attributes = []): Product
    {
        return Product::factory()->create(array_merge([
            'name' => 'Arroz Integral',
            'stock' => 12,
            'min_stock' => 10,
            'max_stock' => 30,
        ], $attributes));
    }

    // ── Listagem no modal ─────────────────────────────────────────────

    public function test_index_lists_near_min_products_in_add_modal(): void
    {
        $this->nearMinProduct();
        Product::factory()->create(['name' => 'Produto Longe', 'stock' => 100, 'min_stock' => 10, 'max_stock' => 100]);
        Product::factory()->create(['name' => 'Produto Abaixo', 'stock' => 5, 'min_stock' => 10, 'max_stock' => 40]);

        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertSee('Adicionar Próximos')
            ->assertSee('Arroz Integral')
            ->assertSee('Produto Abaixo')
            ->assertDontSee('Produto Longe');
    }

    public function test_index_opens_add_modal_automatically_with_query_flag(): void
    {
        $this->nearMinProduct();

        $this->actingAs($this->admin)->get(route('lista-compra.index', ['adicionar' => 1]))
            ->assertOk()
            ->assertSee('data-auto-open="1"', false);
    }

    public function test_dashboard_shows_add_to_list_link(): void
    {
        $this->nearMinProduct();

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Adicionar à Lista')
            ->assertSee('adicionar=1', false);
    }

    // ── Criação de itens ──────────────────────────────────────────────

    public function test_add_items_creates_manual_list_entries(): void
    {
        $product = $this->nearMinProduct();

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => 7],
        ])->assertRedirect(route('lista-compra.index'));

        $this->assertDatabaseHas('purchase_list_items', [
            'product_id' => $product->id,
            'quantity' => 7,
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertSee('Adicionado manualmente')
            ->assertSee('Arroz Integral');
    }

    public function test_add_is_idempotent_when_form_is_resubmitted(): void
    {
        $product = $this->nearMinProduct();

        $payload = [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => 5],
        ];

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), $payload)
            ->assertRedirect(route('lista-compra.index'));

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), $payload)
            ->assertRedirect(route('lista-compra.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_list_items', 1);
        $this->assertDatabaseHas('purchase_list_items', ['product_id' => $product->id, 'quantity' => 5]);
    }

    // ── Skip de abaixo-do-mínimo / duplicados ─────────────────────────

    public function test_add_skips_below_minimum_and_already_listed_products(): void
    {
        $below = Product::factory()->create(['name' => 'Abaixo Min', 'stock' => 2, 'min_stock' => 10, 'max_stock' => 40]);
        $near = $this->nearMinProduct();
        PurchaseListItem::create(['product_id' => $near->id, 'quantity' => 3, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$below->id, $near->id],
            'quantities' => [$below->id => 10, $near->id => 10],
        ])->assertRedirect(route('lista-compra.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_list_items', 1);
        $this->assertDatabaseHas('purchase_list_items', ['product_id' => $near->id, 'quantity' => 3]);
    }

    public function test_add_ignores_products_with_pending_request_in_modal(): void
    {
        $near = $this->nearMinProduct(['name' => 'Produto Pendente']);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $request->items()->create(['product_id' => $near->id, 'quantity' => 10]);

        // Fora do modal de adição (e de qualquer outro ponto da página).
        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertDontSee('Produto Pendente');

        $this->assertDatabaseCount('purchase_list_items', 0);
    }

    // ── Validação de quantidades ──────────────────────────────────────

    public function test_add_validates_required_fields_and_quantities(): void
    {
        $product = $this->nearMinProduct();

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), [])
            ->assertSessionHasErrors(['product_ids', 'quantities']);

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => 0],
        ])->assertSessionHasErrors('quantities.'.$product->id);

        $this->actingAs($this->admin)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => -2],
        ])->assertSessionHasErrors('quantities.'.$product->id);

        $this->assertDatabaseCount('purchase_list_items', 0);
    }

    // ── Remoção ───────────────────────────────────────────────────────

    public function test_destroy_item_removes_from_list(): void
    {
        $product = $this->nearMinProduct();
        $item = PurchaseListItem::create(['product_id' => $product->id, 'quantity' => 8, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->delete(route('lista-compra.destroyItem', $item->id))
            ->assertRedirect(route('lista-compra.index'));

        $this->assertDatabaseCount('purchase_list_items', 0);

        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertDontSee('Adicionado manualmente');
    }

    // ── Consumo na geração de solicitação ─────────────────────────────

    public function test_generate_includes_and_consumes_manual_items(): void
    {
        $manual = $this->nearMinProduct();
        $low = Product::factory()->create(['name' => 'Baixo', 'stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        PurchaseListItem::create(['product_id' => $manual->id, 'quantity' => 6, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'))
            ->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseCount('purchase_requests', 1);
        $this->assertDatabaseCount('purchase_list_items', 0);

        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $manual->id,
            'quantity' => 6,
        ]);
        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $low->id,
            'quantity' => 18,
        ]);
    }

    public function test_generate_uses_custom_quantity_for_manual_item(): void
    {
        $manual = $this->nearMinProduct();

        PurchaseListItem::create(['product_id' => $manual->id, 'quantity' => 9, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$manual->id => 4],
        ])->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $manual->id,
            'quantity' => 4,
        ]);
        $this->assertDatabaseCount('purchase_list_items', 0);
    }

    public function test_generate_keeps_manual_item_when_quantity_is_zeroed(): void
    {
        $manual = $this->nearMinProduct();
        PurchaseListItem::create(['product_id' => $manual->id, 'quantity' => 9, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$manual->id => 0],
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_requests', 0);
        $this->assertDatabaseCount('purchase_list_items', 1);
    }

    public function test_generate_modal_shows_manual_item_with_stored_quantity(): void
    {
        $manual = $this->nearMinProduct();
        PurchaseListItem::create(['product_id' => $manual->id, 'quantity' => 6, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertSee('Gerar Solicitação de Compra')
            ->assertSee('quantities['.$manual->id, false);
    }

    // ── Permissões de operador ────────────────────────────────────────

    public function test_operator_with_permissions_can_add_and_remove(): void
    {
        $product = $this->nearMinProduct();
        $operator = User::factory()->create(['is_admin' => false]);
        $role = Role::firstOrCreate(['name' => 'Operador']);
        $role->syncPermissions(PermissionMap::OPERATOR_PERMISSIONS);
        $operator->assignRole($role);

        $this->actingAs($operator)->get(route('lista-compra.index'))->assertOk();

        $this->actingAs($operator)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => 5],
        ])->assertRedirect(route('lista-compra.index'));

        $item = PurchaseListItem::firstOrFail();

        $this->actingAs($operator)
            ->delete(route('lista-compra.destroyItem', $item->id))
            ->assertRedirect(route('lista-compra.index'));

        $this->assertDatabaseCount('purchase_list_items', 0);
    }

    public function test_user_with_view_only_cannot_add_or_remove(): void
    {
        $product = $this->nearMinProduct();
        $viewer = User::factory()->create(['is_admin' => false]);
        $role = Role::firstOrCreate(['name' => 'Somente Visualização']);
        $role->syncPermissions(['lista-compra.view']);
        $viewer->assignRole($role);

        $this->actingAs($viewer)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertDontSee('Adicionar Próximos');

        $this->actingAs($viewer)->post(route('lista-compra.adicionar'), [
            'product_ids' => [$product->id],
            'quantities' => [$product->id => 5],
        ])->assertForbidden();

        $item = PurchaseListItem::create(['product_id' => $product->id, 'quantity' => 4, 'user_id' => $this->admin->id]);

        $this->actingAs($viewer)
            ->delete(route('lista-compra.destroyItem', $item->id))
            ->assertForbidden();

        $this->assertDatabaseCount('purchase_list_items', 1);
    }

    // ── Exportação ────────────────────────────────────────────────────

    public function test_export_includes_manual_items(): void
    {
        $low = Product::factory()->create(['name' => 'Abaixo Min', 'stock' => 1, 'min_stock' => 10]);
        $manual = $this->nearMinProduct(['name' => 'Manual Export']);
        PurchaseListItem::create(['product_id' => $manual->id, 'quantity' => 5, 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)->get(route('lista-compra.export'))
            ->assertOk()
            ->assertDownload('lista-compra-'.now()->format('d-m-Y').'.xlsx');
    }
}
