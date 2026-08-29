<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_soft_deleted_product_releases_barcode(): void
    {
        $product = Product::factory()->create(['barcode' => '7890000000001']);

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertNull($product->fresh()->barcode);
    }

    public function test_barcode_can_be_reused_after_soft_delete(): void
    {
        $first = Product::factory()->create(['barcode' => '7890000000002']);
        $first->delete();

        $second = Product::factory()->create(['barcode' => '7890000000002']);

        $this->assertDatabaseHas('products', ['id' => $second->id, 'barcode' => '7890000000002']);
    }

    public function test_restored_product_gets_a_new_unique_barcode(): void
    {
        $product = Product::factory()->create(['barcode' => '7890000000003']);
        $product->delete();
        $this->assertNull($product->fresh()->barcode);

        $replacement = Product::factory()->create(['barcode' => '7890000000003']);
        $product->restore();

        $restored = $product->fresh();
        $this->assertNotNull($restored->barcode);
        $this->assertNotEquals('7890000000003', $restored->barcode);
        $this->assertNotEquals($replacement->barcode, $restored->barcode);
    }
}