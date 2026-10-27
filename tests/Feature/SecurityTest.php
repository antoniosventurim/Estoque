<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Support\PermissionMap;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SecurityTest extends TestCase
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

    public function test_phpass_is_not_a_project_dependency(): void
    {
        $lock = file_get_contents(base_path('composer.lock'));
        $composer = file_get_contents(base_path('composer.json'));

        $this->assertStringNotContainsString('"phpass"', $lock);
        $this->assertStringNotContainsString('phpass', $composer);
    }

    public function test_passwords_are_stored_with_modern_hashing(): void
    {
        $user = User::factory()->create(['password' => 'senhaForte123']);
        $raw = $user->getAttributes()['password'];

        $this->assertMatchesRegularExpression('/^\$(2y|2a|2b|argon2)/', $raw);
        $this->assertDoesNotMatchRegularExpression('/^\$[PH]\$/', $raw, 'Hash phpass detectado.');
        $this->assertTrue(Hash::check('senhaForte123', $raw));
    }

    public function test_user_serialization_hides_credentials(): void
    {
        $array = User::factory()->create()->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret123')]);

        for ($i = 1; $i <= 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'errada'.$i])
                ->assertStatus(302);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'errada6'])
            ->assertStatus(429);
    }

    public function test_guests_are_redirected_from_every_admin_route(): void
    {
        $routes = collect(app('router')->getRoutes()->getRoutes())
            ->filter(fn ($route) => in_array('GET', $route->methods(), true))
            ->filter(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->values();

        $this->assertGreaterThan(30, $routes->count());

        foreach ($routes as $route) {
            $uri = '/'.preg_replace('/\{[^}]+\}/', '1', $route->uri());

            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    public function test_user_without_permissions_is_blocked_from_management_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $requests = [
            ['get', route('usuarios')],
            ['post', route('usuarios.store')],
            ['get', route('roles.index')],
            ['post', route('roles.store')],
            ['get', route('configuracoes')],
            ['post', route('configuracoes.save')],
        ];

        foreach ($requests as [$method, $uri]) {
            $this->actingAs($user)->{$method}($uri, $method === 'post' ? ['name' => 'Bloqueado'] : [])
                ->assertForbidden();
        }
    }

    public function test_editor_cannot_escalate_to_admin_on_update(): void
    {
        $editor = $this->createUserWithPermissions(['usuarios.view', 'usuarios.create', 'usuarios.edit']);
        $target = User::factory()->create(['is_admin' => false]);
        Role::firstOrCreate(['name' => 'Administrador']);

        $this->actingAs($editor)
            ->put(route('usuarios.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'is_admin' => 1,
                'roles' => ['Administrador'],
            ])
            ->assertRedirect(route('usuarios'));

        $target->refresh();
        $this->assertFalse((bool) $target->is_admin);
        $this->assertFalse($target->hasRole('Administrador'));
    }

    public function test_editor_cannot_create_an_admin_user(): void
    {
        $editor = $this->createUserWithPermissions(['usuarios.view', 'usuarios.create', 'usuarios.edit']);
        Role::firstOrCreate(['name' => 'Administrador']);

        $this->actingAs($editor)
            ->post(route('usuarios.store'), [
                'name' => 'Escalado',
                'email' => 'escalado@example.com',
                'password' => 'senhaForte123',
                'password_confirmation' => 'senhaForte123',
                'is_admin' => 1,
                'roles' => ['Administrador'],
            ])
            ->assertRedirect(route('usuarios'));

        $created = User::where('email', 'escalado@example.com')->firstOrFail();
        $this->assertFalse((bool) $created->is_admin);
        $this->assertFalse($created->hasRole('Administrador'));
    }

    public function test_admin_can_promote_a_user(): void
    {
        $target = User::factory()->create(['is_admin' => false]);
        Role::firstOrCreate(['name' => 'Administrador']);

        $this->actingAs($this->admin)
            ->put(route('usuarios.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'is_admin' => 1,
                'roles' => ['Administrador'],
            ])
            ->assertRedirect(route('usuarios'));

        $target->refresh();
        $this->assertTrue((bool) $target->is_admin);
        $this->assertTrue($target->hasRole('Administrador'));
    }

    public function test_csrf_middleware_is_enabled_on_web_routes(): void
    {
        $groups = app(\Illuminate\Contracts\Http\Kernel::class)->getMiddlewareGroups();

        $this->assertArrayHasKey('web', $groups);
        $this->assertContains(ValidateCsrfToken::class, $groups['web']);
    }

    public function test_php_upload_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');

        $this->actingAs($this->admin)
            ->post(route('produtos.store'), [
                'name' => 'Malicioso',
                'image' => $file,
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['name' => 'Malicioso']);
    }

    public function test_product_search_is_not_vulnerable_to_sql_injection(): void
    {
        Product::factory()->create(['name' => 'ProdutoAlfa123']);
        Product::factory()->create(['name' => 'ProdutoBeta456']);

        $response = $this->actingAs($this->admin)
            ->get(route('produtos.index', ['q' => "' OR 1=1 --"]));

        $response->assertOk();
        $response->assertDontSee('ProdutoAlfa123');
        $response->assertDontSee('ProdutoBeta456');
    }

    public function test_product_names_are_escaped_in_html(): void
    {
        Product::factory()->create(['name' => '<script>alert(1)</script>']);

        $response = $this->actingAs($this->admin)->get(route('produtos.index'));

        $response->assertOk();
        $response->assertSee('<script>alert(1)</script>');
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_env_file_is_not_tracked_and_example_has_no_app_key(): void
    {
        $gitignoreLines = array_map('trim', file(base_path('.gitignore')));
        $this->assertContains('.env', $gitignoreLines);

        $example = file_get_contents(base_path('.env.example'));
        $this->assertMatchesRegularExpression('/^APP_KEY=$/m', $example);

        exec('git -C '.escapeshellarg(base_path()).' ls-files -- .env 2>/dev/null', $tracked, $code);
        if ($code === 0) {
            $this->assertNotContains('.env', $tracked, 'O arquivo .env está versionado.');
        }
    }

    public function test_session_cookies_use_safe_defaults(): void
    {
        $this->assertTrue(config('session.http_only'));
        $this->assertNotSame('none', config('session.same_site'));
    }

    private function createUserWithPermissions(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'Editor_'.md5(implode('|', $permissions))]);
        $role->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create(['is_admin' => false]);
        $user->assignRole($role);

        return $user;
    }
}
