<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_blocked_from_admin_area(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/produtos')
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get('/admin/saida')
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get('/admin/usuarios')
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/produtos')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/saida')
            ->assertOk();
    }

    public function test_any_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }
}