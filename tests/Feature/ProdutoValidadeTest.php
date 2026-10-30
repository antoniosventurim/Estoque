<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdutoValidadeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_product_form_shows_expiration_field_for_food_category(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->get(route('produtos.edit', $product))
            ->assertOk()
            ->assertSee('expiration_date')
            ->assertSee('Data de validade');
    }

    public function test_product_form_hides_expiration_field_for_normal_category(): void
    {
        $category = Category::create(['name' => 'Ferramentas', 'requires_expiration' => false]);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin)
            ->get(route('produtos.edit', $product))
            ->assertOk()
            ->assertSee('expiration-date-field')
            ->assertSee('hidden', false);
    }

    public function test_product_store_requires_expiration_for_food_category(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);

        $this->actingAs($this->admin)
            ->from(route('produtos.create'))
            ->post(route('produtos.store'), [
                'name' => 'Arroz 5kg',
                'category_id' => $category->id,
                'stock' => 10,
                'unit' => 'un',
            ])
            ->assertRedirect(route('produtos.create'))
            ->assertSessionHasErrors('expiration_date');
    }

    public function test_product_store_saves_expiration_for_food_category(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $expiration = now()->addDays(60)->toDateString();

        $this->actingAs($this->admin)->post(route('produtos.store'), [
            'name' => 'Arroz 5kg',
            'barcode' => '7891111222233',
            'category_id' => $category->id,
            'stock' => 10,
            'unit' => 'un',
            'expiration_date' => $expiration,
        ])->assertRedirect(route('produtos.index'));

        $product = Product::where('barcode', '7891111222233')->first();
        $this->assertNotNull($product);
        $this->assertSame($expiration, $product->expiration_date->toDateString());
    }

    public function test_product_store_does_not_require_expiration_for_normal_category(): void
    {
        $category = Category::create(['name' => 'Ferramentas', 'requires_expiration' => false]);

        $this->actingAs($this->admin)->post(route('produtos.store'), [
            'name' => 'Chave de Fenda',
            'barcode' => '7891111444455',
            'category_id' => $category->id,
            'stock' => 5,
            'unit' => 'un',
        ])->assertRedirect(route('produtos.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Chave de Fenda',
            'expiration_date' => null,
        ]);
    }

    public function test_product_update_requires_expiration_when_switching_to_food_category(): void
    {
        $food = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $tool = Category::create(['name' => 'Ferramentas', 'requires_expiration' => false]);
        $product = Product::factory()->create(['category_id' => $tool->id]);

        $this->actingAs($this->admin)
            ->from(route('produtos.edit', $product))
            ->put(route('produtos.update', $product), [
                'name' => $product->name,
                'barcode' => $product->barcode,
                'category_id' => $food->id,
            ])
            ->assertSessionHasErrors('expiration_date');
    }

    public function test_entrada_alert_includes_product_level_expiration(): void
    {
        $category = Category::create(['name' => 'Alimentos', 'requires_expiration' => true]);
        $product = Product::factory()->create([
            'name' => 'Leite em Pó',
            'category_id' => $category->id,
            'stock' => 8,
            'expiration_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('entrada'))
            ->assertOk()
            ->assertSee('Produtos próximos do vencimento')
            ->assertSee('Leite em Pó');
    }
}
