<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_can_authenticate_using_email(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect(
            route('dashboard', absolute: false)
        );
    }

    public function test_user_can_authenticate_using_username(): void
    {
        $user = User::factory()->create([
            'username' => 'guru.matematika',
        ]);

        $response = $this->post('/login', [
            'login' => 'guru.matematika',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $response->assertRedirect(
            route('dashboard', absolute: false)
        );
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'login' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors('login');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'failed_login_count' => 1,
        ]);
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'status' => 'inactive',
        ]);

        $response = $this->post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors('login');
    }

    public function test_locked_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'failed_login_count' => 5,
            'locked_until' => now()->addMinutes(10),
        ]);

        $response = $this->post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors('login');
    }

    public function test_successful_login_resets_failed_login_data(): void
    {
        $user = User::factory()->create([
            'failed_login_count' => 3,
            'locked_until' => now()->subMinute(),
        ]);

        $this->post('/login', [
            'login' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);

        $user->refresh();

        $this->assertSame(0, $user->failed_login_count);
        $this->assertNull($user->locked_until);
        $this->assertNotNull($user->last_login_at);
        $this->assertNotNull($user->last_login_ip);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/logout');

        $this->assertGuest();

        $response->assertRedirect('/');
    }
}
