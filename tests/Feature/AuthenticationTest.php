<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin()
    {
        $this->get('/admin/applications')->assertRedirect('/login');
    }

    public function test_user_can_log_in_and_reach_admin()
    {
        $user = $this->createUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/admin/applications');

        $this->assertAuthenticatedAs($user);
        $this->get('/admin/applications')->assertOk();
    }

    public function test_authenticated_user_is_redirected_from_login_page()
    {
        $this->actingAs($this->createUser())
            ->get('/login')
            ->assertRedirect('/admin/applications');
    }

    public function test_invalid_credentials_do_not_authenticate_user()
    {
        $user = $this->createUser();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_redirects_to_login_page()
    {
        $this->actingAs($this->createUser())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    private function createUser()
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
