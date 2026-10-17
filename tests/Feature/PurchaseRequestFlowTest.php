<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\Product;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_store_creates_purchase_request_from_low_stock_products(): void
    {
        Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);
        Product::factory()->create(['stock' => 50, 'min_stock' => 5, 'max_stock' => 20]);

        $response = $this->actingAs($this->admin)->post(route('solicitacoes.store'));

        $response->assertRedirect(route('solicitacoes.index'));
        $this->assertDatabaseCount('purchase_requests', 1);
        $this->assertDatabaseCount('purchase_request_items', 1);

        $item = \App\Models\PurchaseRequestItem::first();
        $this->assertEquals(18, $item->quantity);
    }

    public function test_store_skips_products_already_in_pending_request(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_requests', 1);
    }

    public function test_store_accepts_custom_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 0, 'min_stock' => 5, 'max_stock' => 50]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$product->id => 30],
        ])->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $product->id,
            'quantity' => 30,
        ]);
    }

    public function test_store_caps_custom_quantity_at_default(): void
    {
        $product = Product::factory()->create(['stock' => 0, 'min_stock' => 5, 'max_stock' => 50]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$product->id => 999],
        ]);

        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);
    }

    public function test_store_skips_items_with_zero_quantity(): void
    {
        $productA = Product::factory()->create(['stock' => 0, 'min_stock' => 5, 'max_stock' => 50]);
        $productB = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$productA->id => 0, $productB->id => 10],
        ])->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseCount('purchase_request_items', 1);
        $this->assertDatabaseHas('purchase_request_items', [
            'product_id' => $productB->id,
            'quantity' => 10,
        ]);
    }

    public function test_store_rejects_when_all_items_zeroed(): void
    {
        $product = Product::factory()->create(['stock' => 0, 'min_stock' => 5, 'max_stock' => 50]);

        $this->actingAs($this->admin)->post(route('solicitacoes.store'), [
            'quantities' => [$product->id => 0],
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('purchase_requests', 0);
    }

    public function test_deliver_updates_stock_and_creates_in_movement(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $response = $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request));

        $response->assertRedirect(route('solicitacoes.index'));
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 20]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'quantity' => 18,
            'stock_before' => 2,
            'stock_after' => 20,
            'user_id' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $request->id,
            'status' => PurchaseRequest::STATUS_DELIVERED,
            'delivered_by' => $this->admin->id,
        ]);
    }

    public function test_delivered_request_cannot_be_deleted(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request));

        $this->actingAs($this->admin)->delete(route('solicitacoes.destroy', $request))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('purchase_requests', ['id' => $request->id]);
    }

    public function test_partial_receive_updates_stock_and_sets_partial_status(): void
    {
        $productA = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);
        $productB = Product::factory()->create(['stock' => 1, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $itemA = $request->items()->create(['product_id' => $productA->id, 'quantity' => 18]);
        $itemB = $request->items()->create(['product_id' => $productB->id, 'quantity' => 19]);

        $response = $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request), [
            'received' => [$itemA->id => 18, $itemB->id => 0],
        ]);

        $response->assertRedirect(route('solicitacoes.show', $request));
        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock' => 20]);
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'stock' => 1]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $productA->id,
            'type' => Movement::TYPE_IN,
            'quantity' => 18,
        ]);
        $this->assertDatabaseCount('movements', 1);
        $this->assertDatabaseHas('purchase_requests', ['id' => $request->id, 'status' => PurchaseRequest::STATUS_PARTIAL]);
        $this->assertDatabaseHas('purchase_request_items', ['id' => $itemA->id, 'received_quantity' => 18]);
        $this->assertDatabaseHas('purchase_request_items', ['id' => $itemB->id, 'received_quantity' => 0]);
    }

    public function test_partial_receive_cannot_exceed_missing_quantity(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $item = $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request), [
            'received' => [$item->id => 999],
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 20]);
        $this->assertDatabaseHas('purchase_request_items', ['id' => $item->id, 'received_quantity' => 18]);
        $this->assertDatabaseHas('purchase_requests', ['id' => $request->id, 'status' => PurchaseRequest::STATUS_DELIVERED]);
    }

    public function test_partial_then_finalize_receives_remaining_and_delivers(): void
    {
        $productA = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);
        $productB = Product::factory()->create(['stock' => 1, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $itemA = $request->items()->create(['product_id' => $productA->id, 'quantity' => 18]);
        $itemB = $request->items()->create(['product_id' => $productB->id, 'quantity' => 19]);

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request), [
            'received' => [$itemA->id => 10, $itemB->id => 0],
        ]);

        $this->assertDatabaseHas('purchase_requests', ['id' => $request->id, 'status' => PurchaseRequest::STATUS_PARTIAL]);

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request))
            ->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseHas('products', ['id' => $productA->id, 'stock' => 20]);
        $this->assertDatabaseHas('products', ['id' => $productB->id, 'stock' => 20]);
        $this->assertDatabaseHas('purchase_request_items', ['id' => $itemA->id, 'received_quantity' => 18]);
        $this->assertDatabaseHas('purchase_request_items', ['id' => $itemB->id, 'received_quantity' => 19]);
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $request->id,
            'status' => PurchaseRequest::STATUS_DELIVERED,
            'delivered_by' => $this->admin->id,
        ]);
    }

    public function test_partial_request_cannot_be_deleted(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $item = $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request), [
            'received' => [$item->id => 5],
        ]);

        $this->actingAs($this->admin)->delete(route('solicitacoes.destroy', $request))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('purchase_requests', ['id' => $request->id, 'status' => PurchaseRequest::STATUS_PARTIAL]);
    }

    public function test_pending_request_can_be_deleted(): void
    {
        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);

        $this->actingAs($this->admin)->delete(route('solicitacoes.destroy', $request))
            ->assertRedirect(route('solicitacoes.index'));

        $this->assertDatabaseMissing('purchase_requests', ['id' => $request->id]);
    }

    public function test_non_admin_cannot_access_solicitacoes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('solicitacoes.index'))
            ->assertForbidden();
    }

    public function test_pages_render_with_modal_and_tabs(): void
    {
        $product = Product::factory()->create(['stock' => 2, 'min_stock' => 5, 'max_stock' => 20]);
        Product::factory()->create(['stock' => 1, 'min_stock' => 5, 'max_stock' => 20]);

        $request = PurchaseRequest::create(['user_id' => $this->admin->id, 'status' => PurchaseRequest::STATUS_PENDING]);
        $item = $request->items()->create(['product_id' => $product->id, 'quantity' => 18]);

        $this->actingAs($this->admin)->get(route('solicitacoes.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('solicitacoes.show', $request))->assertOk();
        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()
            ->assertSee('Gerar Solicitação de Compra')
            ->assertSee('quantities[', false)
            ->assertDontSee("confirm('Gerar solicitação");

        $this->actingAs($this->admin)->post(route('solicitacoes.deliver', $request), [
            'received' => [$item->id => 5],
        ]);

        $this->actingAs($this->admin)->get(route('solicitacoes.show', $request))
            ->assertOk()
            ->assertSee('Parcial')
            ->assertSee('Faltantes');

        $this->actingAs($this->admin)->get(route('solicitacoes.show', [$request, 'tab' => 'faltantes']))
            ->assertOk()
            ->assertSee('Receber Restante e Finalizar');
    }
}
