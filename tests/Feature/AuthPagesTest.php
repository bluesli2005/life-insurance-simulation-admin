<?php

namespace Tests\Feature;

use App\Http\Controllers\Auth\VerificationController;
use App\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_password_pages_use_the_static_vue_shell()
    {
        $this->assertStaticShell('/login');
        $this->assertStaticShell('/password/reset');
        $this->assertStaticShell('/password/reset/sample-token?email=admin%40example.com');

        $this->actingAs($this->createUser());
        $this->assertStaticShell('/password/confirm');
    }

    public function test_registration_uses_the_vue_shell_and_email_verification_stays_closed()
    {
        $this->assertStaticShell('/register');
        $this->get('/email/verify')->assertNotFound();
    }

    public function test_verification_controller_has_a_static_shell_if_reenabled()
    {
        $this->assertSame(resource_path('spa.html'), (new VerificationController())
            ->show(Request::create('/email/verify'))->getFile()->getPathname());
    }

    public function test_admin_page_uses_static_shell_and_session_endpoint_supplies_permissions()
    {
        $user = $this->createUser();

        $this->actingAs($user);
        $this->assertStaticShell('/admin/applications');
        $this->getJson('/admin/api/v1/session')->assertOk()
            ->assertJsonPath('data.user_name', '管理者')
            ->assertJsonPath('data.role_name', '閲覧者')
            ->assertJsonPath('data.can_write_applications', false)
            ->assertJsonPath('data.can_delete_applications', false)
            ->assertJsonPath('data.can_manage_users', false);
    }

    public function test_password_confirmation_accepts_the_current_password()
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->post('/password/confirm', ['password' => 'password'])
            ->assertRedirect('/admin/applications')
            ->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_forgot_password_sends_a_reset_notification()
    {
        Notification::fake();
        $user = $this->createUser();

        $this->postJson('/password/email', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_returns_a_japanese_password_reset_error()
    {
        $this->postJson('/password/email', ['email' => 'missing@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'このメールアドレスのユーザーが見つかりません。');
    }

    public function test_password_reset_updates_the_password()
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()->assertJsonStructure(['message']);

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_reset_token_returns_a_japanese_validation_error()
    {
        $user = $this->createUser();

        $this->postJson('/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'パスワード再設定トークンが無効です。');
    }

    private function createUser()
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ])->fresh();
    }

    private function assertStaticShell($path)
    {
        $response = $this->get($path)->assertOk()->assertCookie('XSRF-TOKEN');
        $this->assertSame(resource_path('spa.html'), $response->baseResponse->getFile()->getPathname());
    }
}
