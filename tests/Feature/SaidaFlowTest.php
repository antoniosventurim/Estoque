<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaidaFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);
        $this->product = Product::factory()->create(['stock' => 10, 'min_stock' => 2]);
    }

    public function test_saida_with_barcode_finds_product(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/saida?barcode='.$this->product->barcode)
            ->assertOk()
            ->assertSee('Registrar Saída')
            ->assertSee($this->product->name);
    }

    public function test_saida_with_unknown_barcode_shows_not_found(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/saida?barcode=NAOEXISTE')
            ->assertOk()
            ->assertSee('Produto não encontrado')
            ->assertDontSee('Registrar Saída');
    }

    public function test_saida_register_links_selected_employee(): void
    {
        $employee = Employee::create(['name' => 'Funcionário Teste']);
        $costCenter = CostCenter::create(['name' => 'Medicina', 'type' => 'curso']);

        $this->actingAs($this->user)->post('/admin/saida', [
            'barcode' => $this->product->barcode,
            'quantity' => 2,
            'cost_center_id' => $costCenter->id,
            'employee_id' => $employee->id,
        ])->assertRedirect(route('saida'));

        $this->assertDatabaseHas('movements', [
            'product_id' => $this->product->id,
            'type' => 'out',
            'quantity' => 2,
            'employee_id' => $employee->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'stock' => 8,
        ]);
    }

    public function test_saida_register_requires_employee(): void
    {
        $this->actingAs($this->user)->from(route('saida'))->post('/admin/saida', [
            'barcode' => $this->product->barcode,
            'quantity' => 1,
        ])->assertSessionHasErrors(['employee_id', 'cost_center_id']);
    }
}
