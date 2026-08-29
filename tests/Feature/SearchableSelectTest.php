<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchableSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_searchable_selects_render(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $product = Product::factory()->create();

        $this->actingAs($user)->get('/admin/saida?barcode='.$product->barcode)
            ->assertOk()->assertSee('data-searchable-select', false);
        $this->actingAs($user)->get('/admin/produtos/novo')
            ->assertOk()->assertSee('data-searchable-select', false);
        $this->actingAs($user)->get('/admin/movimentacoes')
            ->assertOk()->assertSee('data-searchable-select', false);
        $this->actingAs($user)->get('/admin/centros-custo/novo')
            ->assertOk()->assertSee('data-searchable-select', false);
    }
}
