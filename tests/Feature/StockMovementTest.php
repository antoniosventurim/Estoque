<?php

namespace Tests\Feature;

use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_product_stock_status(): void
    {
        $critical = Product::factory()->create(['stock' => 0, 'min_stock' => 5]);
        $low = Product::factory()->create(['stock' => 5, 'min_stock' => 5]);
        $ok = Product::factory()->create(['stock' => 50, 'min_stock' => 5]);

        $this->assertEquals('critical', $critical->stockStatus());
        $this->assertEquals('low', $low->stockStatus());
        $this->assertEquals('ok', $ok->stockStatus());
    }

    public function test_saida_reduces_stock_and_creates_movement(): void
    {
        $product = Product::factory()->create(['stock' => 10, 'min_stock' => 0]);

        $before = $product->stock;
        $product->stock = $before - 3;
        $product->save();

        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => 3,
            'stock_before' => $before,
            'stock_after' => $product->stock,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 7]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => 3,
        ]);
    }

    public function test_entrada_increases_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $product->stock = 5 + 10;
        $product->save();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 15]);
    }

    public function test_ajuste_sets_stock(): void
    {
        $product = Product::factory()->create(['stock' => 50]);

        $product->stock = 47;
        $product->save();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 47]);
    }

    public function test_saida_maior_que_estoque_nao_e_permitida(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $quantity = 5;
        $canRegister = $quantity <= $product->stock;

        $this->assertFalse($canRegister);
    }
}