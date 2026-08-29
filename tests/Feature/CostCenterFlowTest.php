<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Movement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostCenterFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);
        $this->product = Product::factory()->create(['stock' => 10]);
    }

    public function test_index_and_create_centers(): void
    {
        CostCenter::create(['name' => 'Medicina', 'type' => 'curso']);

        $this->actingAs($this->user)
            ->get('/admin/centros-custo')
            ->assertOk()
            ->assertSee('Medicina');

        $this->actingAs($this->user)
            ->get('/admin/centros-custo/novo')
            ->assertOk()
            ->assertSee('Centro de Custo');
    }

    public function test_saida_registers_with_cost_center(): void
    {
        $costCenter = CostCenter::create(['name' => 'Medicina', 'type' => 'curso', 'code' => 'MED']);

        $this->actingAs($this->user)
            ->post('/admin/saida', [
                'barcode' => $this->product->barcode,
                'quantity' => 1,
                'cost_center_id' => $costCenter->id,
                'user_id' => $this->user->id,
            ])
            ->assertRedirect(route('saida'));

        $this->assertDatabaseHas('movements', [
            'product_id' => $this->product->id,
            'type' => Movement::TYPE_OUT,
            'cost_center_id' => $costCenter->id,
        ]);
    }

    public function test_saida_preview_shows_cost_center_select(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/saida?barcode='.$this->product->barcode)
            ->assertOk()
            ->assertSee('cost_center_id')
            ->assertSee('Centro de Custo');
    }
}
