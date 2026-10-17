<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\PermissionMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolesFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
        PermissionMap::syncPermissions();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_with_view_permission_can_access_but_not_edit(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $product = \App\Models\Product::factory()->create();
        $role = Role::create(['name' => 'Estoquista']);
        $role->givePermissionTo('produtos.view');
        $user->assignRole($role);

        $this->actingAs($user)->get(route('produtos.index'))->assertOk();
        $this->actingAs($user)->get(route('produtos.create'))->assertForbidden();
        $this->actingAs($user)->get(route('produtos.edit', $product))->assertForbidden();
        $this->actingAs($user)->delete(route('produtos.destroy', $product))->assertForbidden();
    }

    public function test_user_with_edit_permission_can_access_edit_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $role = Role::create(['name' => 'Editor']);
        $role->givePermissionTo(['produtos.view', 'produtos.edit', 'solicitacoes.view', 'solicitacoes.receive']);
        $user->assignRole($role);

        $this->actingAs($user)->get(route('produtos.index'))->assertOk();
        $this->actingAs($user)->get(route('solicitacoes.index'))->assertOk();
        $this->actingAs($user)->post(route('solicitacoes.store'))->assertForbidden();
    }

    public function test_user_without_any_permission_cannot_access_modules(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get(route('produtos.index'))->assertForbidden();
        $this->actingAs($user)->get(route('saida'))->assertForbidden();
        $this->actingAs($user)->get(route('roles.index'))->assertForbidden();
    }

    public function test_admin_bypasses_all_permissions(): void
    {
        $this->actingAs($this->admin)->get(route('produtos.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('roles.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('configuracoes'))->assertOk();
    }

    public function test_role_crud_creates_role_with_permissions(): void
    {
        $response = $this->actingAs($this->admin)->post(route('roles.store'), [
            'name' => 'Supervisor',
            'permissions' => ['produtos.view', 'produtos.edit', 'solicitacoes.view'],
        ]);

        $response->assertRedirect(route('roles.index'));
        $this->assertDatabaseHas('roles', ['name' => 'Supervisor']);

        $role = Role::where('name', 'Supervisor')->first();
        $this->assertEqualsCanonicalizing(
            ['produtos.view', 'produtos.edit', 'solicitacoes.view'],
            $role->permissions->pluck('name')->all()
        );
    }

    public function test_role_update_syncs_permissions(): void
    {
        $role = Role::create(['name' => 'Ajudante']);
        $role->givePermissionTo('produtos.view');

        $this->actingAs($this->admin)->get(route('roles.edit', $role))
            ->assertOk()
            ->assertSee('Permissões por módulo')
            ->assertSee('produtos.view', false);

        $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name' => 'Ajudante',
            'permissions' => ['entrada.view', 'entrada.create'],
        ])->assertRedirect(route('roles.index'));

        $this->assertEqualsCanonicalizing(
            ['entrada.view', 'entrada.create'],
            $role->permissions()->pluck('name')->all()
        );
    }

    public function test_role_can_have_all_permissions_removed(): void
    {
        $role = Role::create(['name' => 'Somente Leitura']);
        $role->givePermissionTo('produtos.view', 'entrada.view');

        $this->actingAs($this->admin)->put(route('roles.update', $role), [
            'name' => 'Somente Leitura',
        ])->assertSessionHasNoErrors()->assertRedirect(route('roles.index'));

        $this->assertEquals(0, $role->permissions()->count());
    }

    public function test_builtin_admin_role_cannot_be_deleted(): void
    {
        $role = Role::firstOrCreate(['name' => 'Administrador']);

        $this->actingAs($this->admin)->delete(route('roles.destroy', $role))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'Administrador']);
    }

    public function test_role_can_be_deleted(): void
    {
        $role = Role::create(['name' => 'Temporario']);

        $this->actingAs($this->admin)->delete(route('roles.destroy', $role))
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseMissing('roles', ['name' => 'Temporario']);
    }

    public function test_user_role_assignment_via_usuario_controller(): void
    {
        $role = Role::create(['name' => 'Operador Teste']);
        $role->givePermissionTo('produtos.view');

        $this->actingAs($this->admin)->post(route('usuarios.store'), [
            'name' => 'Maria Permissões',
            'email' => 'maria.perm@test.local',
            'password' => 'senha12345',
            'password_confirmation' => 'senha12345',
            'roles' => ['Operador Teste'],
        ])->assertRedirect(route('usuarios'));

        $user = User::where('email', 'maria.perm@test.local')->first();
        $this->assertTrue($user->hasRole('Operador Teste'));

        $this->actingAs($this->admin)->get(route('usuarios.create'))
            ->assertOk()
            ->assertSee('Perfis de acesso', false);
        $this->actingAs($this->admin)->get(route('usuarios.edit', $user))
            ->assertOk()
            ->assertSee('Perfis de acesso', false)
            ->assertSee('Acesso total (Administrador)', false);

        $this->actingAs($user)->get(route('produtos.index'))->assertOk();
        $this->actingAs($user)->get(route('produtos.create'))->assertForbidden();
    }

    public function test_permission_map_covers_all_modules(): void
    {
        $this->assertNotEmpty(PermissionMap::all());
        $this->assertEquals(
            count(PermissionMap::all()),
            Permission::count()
        );
    }
}
