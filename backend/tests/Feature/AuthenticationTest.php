<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_receives_json_401_even_without_accept_header()
    {
        $this->get('/api/v1/auth/session')->assertUnauthorized()->assertJsonStructure(['message']);
    }

    public function test_user_can_log_in_and_read_session()
    {
        $user = $this->createUser();
        $this->post('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password', 'remember' => false])
            ->assertNoContent();
        $this->assertAuthenticatedAs($user);
        $this->getJson('/api/v1/auth/session')->assertOk();
    }

    public function test_authenticated_user_cannot_use_guest_auth_api()
    {
        $this->actingAs($this->createUser())->post('/api/v1/auth/login', [])->assertStatus(409);
    }

    public function test_invalid_credentials_return_validation_without_authentication()
    {
        $user = $this->createUser();
        $this->post('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_logout_invalidates_session_without_redirect()
    {
        $this->actingAs($this->createUser())->post('/api/v1/auth/logout')->assertNoContent();
        $this->assertGuest();
        $this->get('/api/v1/auth/session')->assertUnauthorized();
    }

    public function test_login_attempts_are_rate_limited()
    {
        $user = $this->createUser();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
                ->assertStatus(422);
        }
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertStatus(429)->assertJsonValidationErrors('email');
    }

    private function createUser()
    {
        return User::create(['name' => '管理者', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
    }
}
