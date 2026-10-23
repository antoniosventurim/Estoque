<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CostCenter;
use App\Models\Employee;
use App\Models\Movement;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FullApplicationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    // ── Autenticação ──────────────────────────────────────────────────

    public function test_root_redirects_to_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_page_renders_for_guests(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_login_authenticates_and_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_with_invalid_credentials_fails(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'errada'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $this->actingAs($this->admin)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get('/admin/produtos')->assertRedirect('/login');
    }

    // ── Dashboard ─────────────────────────────────────────────────────

    public function test_dashboard_renders_with_stats(): void
    {
        $low = Product::factory()->create(['name' => 'Reagente Crítico', 'stock' => 1, 'min_stock' => 5]);
        Product::factory()->create(['stock' => 30, 'min_stock' => 0]);

        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Reagente Crítico');
    }

    // ── Produtos ──────────────────────────────────────────────────────

    public function test_products_index_and_create_pages_render(): void
    {
        Product::factory()->create(['name' => 'Produto Listado']);

        $this->actingAs($this->admin)->get(route('produtos.index'))
            ->assertOk()->assertSee('Produto Listado');

        $this->actingAs($this->admin)->get(route('produtos.create'))->assertOk();
    }

    public function test_product_store_creates_product(): void
    {
        $category = Category::create(['name' => 'Laboratório']);

        $this->actingAs($this->admin)->post(route('produtos.store'), [
            'name' => 'Bequer 500ml',
            'barcode' => '9990001112223',
            'category_id' => $category->id,
            'stock' => 12,
            'min_stock' => 2,
            'unit' => 'un',
        ])->assertRedirect(route('produtos.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Bequer 500ml',
            'barcode' => '9990001112223',
            'stock' => 12,
        ]);
    }

    public function test_product_store_validates_required_name(): void
    {
        $this->actingAs($this->admin)->from(route('produtos.create'))->post(route('produtos.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_product_update_edits_product(): void
    {
        $product = Product::factory()->create(['name' => 'Nome Antigo']);

        $this->actingAs($this->admin)->put(route('produtos.update', $product), [
            'name' => 'Nome Novo',
            'barcode' => $product->barcode,
        ])->assertRedirect(route('produtos.index'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Nome Novo']);
    }

    public function test_product_destroy_soft_deletes(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->delete(route('produtos.destroy', $product))
            ->assertRedirect(route('produtos.index'));

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_bulk_destroy_removes_many(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();

        $this->actingAs($this->admin)->post(route('produtos.bulkDestroy'), [
            'ids' => [$a->id, $b->id],
        ])->assertRedirect(route('produtos.index'));

        $this->assertSoftDeleted('products', ['id' => $a->id]);
        $this->assertSoftDeleted('products', ['id' => $b->id]);
    }

    public function test_product_barcode_returns_png(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->admin)->get(route('produtos.barcode', $product))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    // ── Categorias ────────────────────────────────────────────────────

    public function test_category_full_crud(): void
    {
        $this->actingAs($this->admin)->post(route('categorias.store'), ['name' => 'Química'])
            ->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Química']);

        $category = Category::where('name', 'Química')->first();

        $this->actingAs($this->admin)->put(route('categorias.update', $category), ['name' => 'Química Geral'])
            ->assertRedirect(route('categorias.index'));
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Química Geral']);

        $this->actingAs($this->admin)->get(route('categorias.index'))->assertOk()->assertSee('Química Geral');
        $this->actingAs($this->admin)->get(route('categorias.edit', $category))->assertOk();

        $this->actingAs($this->admin)->delete(route('categorias.destroy', $category))
            ->assertRedirect(route('categorias.index'));
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
    }

    public function test_category_rejects_duplicate_name(): void
    {
        Category::create(['name' => 'Duplicada']);

        $this->actingAs($this->admin)->from(route('categorias.create'))->post(route('categorias.store'), [
            'name' => 'Duplicada',
        ])->assertSessionHasErrors('name');
    }

    // ── Unidades ──────────────────────────────────────────────────────

    public function test_unit_full_crud(): void
    {
        $this->actingAs($this->admin)->post(route('unidades.store'), [
            'name' => 'Caixa',
            'abbreviation' => 'CX2',
        ])->assertRedirect(route('unidades.index'));
        $this->assertDatabaseHas('units', ['name' => 'Caixa', 'abbreviation' => 'CX2']);

        $unit = Unit::where('abbreviation', 'CX2')->first();

        $this->actingAs($this->admin)->put(route('unidades.update', $unit), [
            'name' => 'Caixa Grande',
            'abbreviation' => 'CX2',
        ])->assertRedirect(route('unidades.index'));
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => 'Caixa Grande']);

        $this->actingAs($this->admin)->get(route('unidades.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('unidades.edit', $unit))->assertOk();

        $this->actingAs($this->admin)->delete(route('unidades.destroy', $unit))
            ->assertRedirect(route('unidades.index'));
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }

    // ── Funcionários ──────────────────────────────────────────────────

    public function test_employee_full_crud(): void
    {
        $this->actingAs($this->admin)->post(route('funcionarios.store'), ['name' => 'Maria Silva'])
            ->assertRedirect(route('funcionarios.index'));
        $this->assertDatabaseHas('employees', ['name' => 'Maria Silva']);

        $employee = Employee::where('name', 'Maria Silva')->first();

        $this->actingAs($this->admin)->put(route('funcionarios.update', $employee), ['name' => 'Maria Souza'])
            ->assertRedirect(route('funcionarios.index'));
        $this->assertDatabaseHas('employees', ['id' => $employee->id, 'name' => 'Maria Souza']);

        $this->actingAs($this->admin)->get(route('funcionarios.index'))->assertOk()->assertSee('Maria Souza');
        $this->actingAs($this->admin)->get(route('funcionarios.create'))->assertOk();

        $this->actingAs($this->admin)->delete(route('funcionarios.destroy', $employee))
            ->assertRedirect(route('funcionarios.index'));
        $this->assertDatabaseMissing('employees', ['id' => $employee->id]);
    }

    // ── Centros de Custo ──────────────────────────────────────────────

    public function test_cost_center_full_crud(): void
    {
        $this->actingAs($this->admin)->post(route('centros-custo.store'), [
            'name' => 'Biblioteca',
            'type' => 'setor',
            'code' => 'BIB',
        ])->assertRedirect(route('centros-custo.index'));
        $this->assertDatabaseHas('cost_centers', ['name' => 'Biblioteca', 'type' => 'setor']);

        $costCenter = CostCenter::where('name', 'Biblioteca')->first();

        $this->actingAs($this->admin)->put(route('centros-custo.update', $costCenter), [
            'name' => 'Biblioteca Central',
            'type' => 'setor',
        ])->assertRedirect(route('centros-custo.index'));
        $this->assertDatabaseHas('cost_centers', ['id' => $costCenter->id, 'name' => 'Biblioteca Central']);

        $this->actingAs($this->admin)->get(route('centros-custo.edit', $costCenter))->assertOk();

        $this->actingAs($this->admin)->delete(route('centros-custo.destroy', $costCenter))
            ->assertRedirect(route('centros-custo.index'));
        $this->assertSoftDeleted('cost_centers', ['id' => $costCenter->id]);
    }

    public function test_cost_center_rejects_invalid_type(): void
    {
        $this->actingAs($this->admin)->from(route('centros-custo.create'))->post(route('centros-custo.store'), [
            'name' => 'Inválido',
            'type' => 'qualquer',
        ])->assertSessionHasErrors('type');
    }

    // ── Usuários ──────────────────────────────────────────────────────

    public function test_user_full_crud_with_role(): void
    {
        Role::firstOrCreate(['name' => 'Comprador']);

        $this->actingAs($this->admin)->post(route('usuarios.store'), [
            'name' => 'João Compras',
            'email' => 'joao@example.com',
            'password' => 'senha1234',
            'password_confirmation' => 'senha1234',
            'roles' => ['Comprador'],
        ])->assertRedirect(route('usuarios'));

        $user = User::where('email', 'joao@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Comprador'));

        $this->actingAs($this->admin)->get(route('usuarios'))->assertOk()->assertSee('João Compras');
        $this->actingAs($this->admin)->get(route('usuarios.create'))->assertOk();
        $this->actingAs($this->admin)->get(route('usuarios.edit', $user))->assertOk();

        $this->actingAs($this->admin)->put(route('usuarios.update', $user), [
            'name' => 'João Atualizado',
            'email' => 'joao@example.com',
        ])->assertRedirect(route('usuarios'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'João Atualizado']);

        $this->actingAs($this->admin)->delete(route('usuarios.destroy', $user))
            ->assertRedirect(route('usuarios'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_user_store_requires_password(): void
    {
        $this->actingAs($this->admin)->from(route('usuarios.create'))->post(route('usuarios.store'), [
            'name' => 'Sem Senha',
            'email' => 'semsenha@example.com',
        ])->assertSessionHasErrors('password');
    }

    public function test_user_cannot_delete_self(): void
    {
        $this->actingAs($this->admin)->delete(route('usuarios.destroy', $this->admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'deleted_at' => null]);
    }

    // ── Entrada ───────────────────────────────────────────────────────

    public function test_entrada_page_renders_and_registers_stock_in(): void
    {
        $product = Product::factory()->create(['stock' => 5]);
        $costCenter = CostCenter::create(['name' => 'Farmácia', 'type' => 'setor']);

        $this->actingAs($this->admin)->get(route('entrada'))
            ->assertOk();

        $this->actingAs($this->admin)->get(route('entrada', ['barcode' => $product->barcode]))
            ->assertOk()->assertSee($product->name);

        $this->actingAs($this->admin)->post(route('entrada.register'), [
            'barcode' => $product->barcode,
            'quantity' => 10,
            'cost_center_id' => $costCenter->id,
        ])->assertRedirect(route('entrada'));

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 15]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => Movement::TYPE_IN,
            'quantity' => 10,
            'cost_center_id' => $costCenter->id,
        ]);
    }

    public function test_entrada_rejects_unknown_barcode(): void
    {
        $this->actingAs($this->admin)->from(route('entrada'))->post(route('entrada.register'), [
            'barcode' => 'INEXISTENTE',
            'quantity' => 1,
        ])->assertSessionHas('error');
    }

    // ── Saída em lote ─────────────────────────────────────────────────

    public function test_saida_batch_registers_multiple_items(): void
    {
        $a = Product::factory()->create(['stock' => 10]);
        $b = Product::factory()->create(['stock' => 8]);
        $employee = Employee::create(['name' => 'Pedro Operador']);
        $costCenter = CostCenter::create(['name' => 'TI', 'type' => 'setor']);

        $this->actingAs($this->admin)->post(route('saida.batch'), [
            'items' => [
                ['barcode' => $a->barcode, 'quantity' => 2],
                ['barcode' => $b->barcode, 'quantity' => 3],
            ],
            'cost_center_id' => $costCenter->id,
            'employee_id' => $employee->id,
        ])->assertRedirect(route('saida'));

        $this->assertDatabaseHas('products', ['id' => $a->id, 'stock' => 8]);
        $this->assertDatabaseHas('products', ['id' => $b->id, 'stock' => 5]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $a->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => 2,
            'employee_id' => $employee->id,
            'cost_center_id' => $costCenter->id,
        ]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $b->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => 3,
        ]);
    }

    public function test_saida_batch_rejects_empty_items(): void
    {
        $employee = Employee::create(['name' => 'Ana']);
        $costCenter = CostCenter::create(['name' => 'RH', 'type' => 'setor']);

        $this->actingAs($this->admin)->from(route('saida'))->post(route('saida.batch'), [
            'items' => [],
            'cost_center_id' => $costCenter->id,
            'employee_id' => $employee->id,
        ])->assertSessionHasErrors('items');
    }

    public function test_saida_batch_reports_insufficient_stock(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        $employee = Employee::create(['name' => 'Ana']);
        $costCenter = CostCenter::create(['name' => 'RH', 'type' => 'setor']);

        $this->actingAs($this->admin)->from(route('saida'))->post(route('saida.batch'), [
            'items' => [['barcode' => $product->barcode, 'quantity' => 50]],
            'cost_center_id' => $costCenter->id,
            'employee_id' => $employee->id,
        ])->assertSessionHas('error');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1]);
    }

    // ── Busca de produtos (JSON) ──────────────────────────────────────

    public function test_saida_search_returns_products_json(): void
    {
        Product::factory()->create(['name' => 'Cadeira Ergonômica Preta']);

        $this->actingAs($this->admin)->get(route('saida.search', ['q' => 'Ergonômica']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Cadeira Ergonômica Preta']);
    }

    public function test_saida_search_requires_minimum_length(): void
    {
        $this->actingAs($this->admin)->get(route('saida.search', ['q' => 'a']))
            ->assertOk()
            ->assertExactJson([]);
    }

    // ── Inventário ────────────────────────────────────────────────────

    public function test_inventario_adjusts_stock(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->actingAs($this->admin)->get(route('inventario'))->assertOk();

        $this->actingAs($this->admin)->post(route('inventario.register'), [
            'product_id' => $product->id,
            'adjustment' => 3,
        ])->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 8]);
        $this->assertDatabaseHas('movements', [
            'product_id' => $product->id,
            'type' => Movement::TYPE_ADJUST,
            'quantity' => 3,
        ]);
    }

    public function test_inventario_rejects_adjustment_below_zero(): void
    {
        $product = Product::factory()->create(['stock' => 2]);

        $this->actingAs($this->admin)->from(route('inventario'))->post(route('inventario.register'), [
            'product_id' => $product->id,
            'adjustment' => -10,
        ])->assertSessionHasErrors('adjustment');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2]);
    }

    // ── Consultas: movimentações, relatórios, lista de compra ─────────

    public function test_movimentacoes_page_renders_with_filters(): void
    {
        $product = Product::factory()->create();
        Movement::create([
            'product_id' => $product->id,
            'type' => Movement::TYPE_OUT,
            'quantity' => 1,
            'stock_before' => 10,
            'stock_after' => 9,
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)->get(route('movimentacoes'))->assertOk();

        $this->actingAs($this->admin)->get(route('movimentacoes', ['type' => 'out', 'q' => $product->name]))
            ->assertOk();
    }

    public function test_relatorios_page_renders_with_period(): void
    {
        Product::factory()->create();

        $this->actingAs($this->admin)->get(route('relatorios'))->assertOk();

        $this->actingAs($this->admin)->get(route('relatorios', [
            'from' => now()->subDays(7)->format('d-m-Y'),
            'to' => now()->format('d-m-Y'),
        ]))->assertOk();
    }

    public function test_relatorios_rejects_inverted_date_range(): void
    {
        Product::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('relatorios'))
            ->get(route('relatorios', [
                'from' => now()->addDay()->format('d-m-Y'),
                'to' => now()->format('d-m-Y'),
            ]))
            ->assertRedirect(route('relatorios'))
            ->assertSessionHasErrors('from');
    }

    public function test_relatorios_rejects_invalid_date_format(): void
    {
        Product::factory()->create();

        $this->actingAs($this->admin)
            ->from(route('relatorios'))
            ->get(route('relatorios', ['from' => '2026-10-23']))
            ->assertRedirect(route('relatorios'))
            ->assertSessionHasErrors('from');
    }

    public function test_lista_compra_index_and_export(): void
    {
        Product::factory()->create(['name' => 'Papel A4', 'stock' => 1, 'min_stock' => 10]);

        $this->actingAs($this->admin)->get(route('lista-compra.index'))
            ->assertOk()->assertSee('Papel A4');

        $this->actingAs($this->admin)->get(route('lista-compra.export'))
            ->assertOk();
    }

    // ── Etiquetas ─────────────────────────────────────────────────────

    public function test_etiquetas_index_and_print(): void
    {
        $product = Product::factory()->create(['name' => 'Etiqueta Teste']);

        $this->actingAs($this->admin)->get(route('etiquetas.index'))
            ->assertOk()->assertSee('Etiqueta Teste');

        $this->actingAs($this->admin)->get(route('etiquetas.print', ['ids' => [$product->id]]))
            ->assertOk()->assertSee('Etiqueta Teste');
    }

    // ── Configurações ─────────────────────────────────────────────────

    public function test_settings_index_and_save(): void
    {
        $this->actingAs($this->admin)->get(route('configuracoes'))->assertOk();

        $this->actingAs($this->admin)->from(route('configuracoes'))->post(route('configuracoes.save'), [
            'empresa_nome' => 'Instituto São Mateus',
            'empresa_cnpj' => '12.345.678/0001-90',
            'estoque_minimo_padrao' => 5,
            'alerta_estoque_acima_minimo' => 40,
        ])->assertRedirect(route('configuracoes'))->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'empresa_nome', 'value' => 'Instituto São Mateus']);
        $this->assertSame('Instituto São Mateus', Setting::get('empresa_nome'));
    }
}
