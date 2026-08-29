<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_product_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['is_admin' => true]);
        $barcode = 'IMG-'.uniqid();

        $this->actingAs($user)->post('/admin/produtos', [
            'name' => 'Produto Imagem Teste',
            'barcode' => $barcode,
            'stock' => 5,
            'min_stock' => 1,
            'unit' => 'un',
            'image' => UploadedFile::fake()->image('foto.png', 10, 10),
        ])->assertRedirect();

        $product = Product::where('barcode', $barcode)->first();
        $this->assertNotNull($product, 'produto deve ser criado');
        $this->assertNotNull($product->image, 'imagem deve ser salva');
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_saida_preview_shows_image(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create(['image' => 'produtos/foto.png']);

        $this->actingAs($user)
            ->get('/admin/saida?barcode='.$product->barcode)
            ->assertOk()
            ->assertSee('storage/'.$product->image);
    }
}
