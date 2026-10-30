<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CostCenter;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntradaValidadeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private CostCenter $costCenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->costCenter = CostCenter::create(['name' => 'Cozinha', 'type' => 'setor']);
    }

    public function test_category_requires_expiration_flag_is_saved(): void
    {
        $this->actingAs($this->admin)->post(route('categorias.store'), [
            'name' => 'Alimentos',
            'requires_expiration' => 1,
        ])->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Alimentos',
            'requires_expiration' => true,
        ]);
    }

    public function test_entrada_requires_expiration_date_for_food_category(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create([
            'stock' => 5,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin)->from(route('entrada', ['barcode' => $product->barcode]))
            ->post(route('entrada.register'), [
                'barcode' => $product->barcode,
                'quantity' => 10,
                'cost_center_id' => $this->costCenter->id,
            ])
            ->assertRedirect(route('entrada', ['barcode' => $product->barcode]))
            ->assertSessionHasErrors('expiration_date');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5]);
    }

    public function test_entrada_saves_expiration_date_for_food_category(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create([
            'stock' => 5,
            'category_id' => $category->id,
        ]);
        $expiration = now()->addDays(45)->toDateString();

        $this->actingAs($this->admin)->post(route('entrada.register'), [
            'barcode' => $product->barcode,
            'quantity' => 10,
            'cost_center_id' => $this->costCenter->id,
            'expiration_date' => $expiration,
        ])->assertRedirect(route('entrada'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 15]);

        $movement = Movement::where('product_id', $product->id)
            ->where('type', Movement::TYPE_IN)
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame($expiration, $movement->expiration_date->toDateString());
    }

    public function test_entrada_does_not_require_expiration_for_normal_category(): void
    {
        $category = Category::create(['name' => 'Ferramentas', 'requires_expiration' => false]);
        $product = Product::factory()->create([
            'stock' => 5,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin)->post(route('entrada.register'), [
            'barcode' => $product->barcode,
            'quantity' => 3,
            'cost_center_id' => $this->costCenter->id,
        ])->assertRedirect(route('entrada'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'expiration_date' => null,
        ]);
    }

    public function test_entrada_form_shows_expiration_field_for_food_product(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create([
            'stock' => 5,
            'category_id' => $category->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('entrada', ['barcode' => $product->barcode]))
            ->assertOk()
            ->assertSee('expiration_date')
            ->assertSee('Data de validade');
    }

    public function test_entrada_page_shows_expiring_products_alert(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create([
            'stock' => 10,
            'category_id' => $category->id,
            'name' => 'Leite Integral',
        ]);

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'quantity' => 10,
            'stock_before' => 0,
            'stock_after' => 10,
            'user_id' => $this->admin->id,
            'expiration_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('entrada'))
            ->assertOk()
            ->assertSee('Produtos próximos do vencimento')
            ->assertSee('Leite Integral');
    }

    public function test_entrada_page_hides_alert_when_no_expiring_products(): void
    {
        $this->actingAs($this->admin)
            ->get(route('entrada'))
            ->assertOk()
            ->assertDontSee('Produtos próximos do vencimento');
    }
}
