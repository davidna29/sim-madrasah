<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveAccountMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_dashboard(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'locked_until' => null,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_inactive_logged_in_user_is_logged_out(): void
    {
        $user = User::factory()->create([
            'status' => 'inactive',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_locked_logged_in_user_is_logged_out(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'locked_until' => now()->addMinutes(10),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard');

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
