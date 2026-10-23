<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['is_admin' => true]);
    }

    public function test_logs_created_product_with_causer(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'estoque',
            'event' => 'created',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
        ]);
    }

    public function test_logs_updated_category_with_old_and_new_values(): void
    {
        $this->actingAs($this->user);

        $category = Category::create(['name' => 'Bebidas']);
        $category->update(['name' => 'Alimentos']);

        $activity = Activity::query()
            ->where('event', 'updated')
            ->where('subject_type', Category::class)
            ->where('subject_id', $category->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame('Alimentos', $activity->attribute_changes->get('attributes')['name'] ?? null);
        $this->assertSame('Bebidas', $activity->attribute_changes->get('old')['name'] ?? null);
    }

    public function test_logs_deleted_product(): void
    {
        $this->actingAs($this->user);

        $product = Product::factory()->create();
        $product->delete();

        $this->assertDatabaseHas('activity_log', [
            'event' => 'deleted',
            'subject_type' => Product::class,
            'subject_id' => $product->id,
        ]);
    }

    public function test_does_not_log_password_changes(): void
    {
        $this->actingAs($this->user);

        $this->user->update(['name' => 'Nome Alterado', 'password' => 'novasenha123']);

        $activity = Activity::query()
            ->where('event', 'updated')
            ->where('subject_type', User::class)
            ->where('subject_id', $this->user->id)
            ->first();

        $this->assertNotNull($activity);

        $attributes = $activity->attribute_changes->get('attributes') ?? [];

        $this->assertArrayNotHasKey('password', $attributes);
        $this->assertSame('Nome Alterado', $attributes['name'] ?? null);
    }

    public function test_settings_tab_shows_on_configuration_page(): void
    {
        $this->actingAs($this->user)
            ->get('/admin/configuracoes')
            ->assertOk()
            ->assertSee('Registros de Atividade');
    }

    public function test_registers_page_shows_logged_activity(): void
    {
        Category::create(['name' => 'Bebidas']);

        $this->actingAs($this->user)
            ->get('/admin/configuracoes/registros')
            ->assertOk()
            ->assertSee('Registros de Atividade')
            ->assertSee('Bebidas');
    }

    public function test_registers_page_filters_by_event(): void
    {
        $category = Category::create(['name' => 'Bebidas']);
        $category->update(['name' => 'Alimentos']);

        $this->actingAs($this->user)
            ->get('/admin/configuracoes/registros?event=updated')
            ->assertOk()
            ->assertSee('Alimentos')
            ->assertDontSee('criou Categoria');
    }

    public function test_registers_page_forbidden_without_permission(): void
    {
        $operator = User::factory()->create(['is_admin' => false]);

        $this->actingAs($operator)
            ->get('/admin/configuracoes/registros')
            ->assertForbidden();
    }
}
