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

    public function test_backend_does_not_serve_frontend_pages()
    {
        foreach (['/login', '/register', '/admin/applications', '/password/reset/token', '/email/verify'] as $path) {
            $this->get($path)->assertNotFound()->assertJsonStructure(['message']);
        }
    }

    public function test_reset_notification_points_to_frontend()
    {
        config(['app.frontend_url' => 'http://localhost:8080']);
        $user = $this->createUser();
        $mail = (new ResetPassword('sample-token'))->toMail($user);
        $this->assertSame('http://localhost:8080/password/reset/sample-token?email=admin%40example.com', $mail->actionUrl);
    }

    public function test_closed_verification_controller_does_not_read_frontend_files()
    {
        $this->assertSame(404, (new VerificationController())->show(Request::create('/email/verify'))->getStatusCode());
    }

    public function test_session_endpoint_supplies_database_permissions()
    {
        $this->actingAs($this->createUser())->getJson('/api/v1/auth/session')->assertOk()
            ->assertJsonPath('data.user_name', '管理者')
            ->assertJsonPath('data.role_name', '閲覧者')
            ->assertJsonPath('data.can_write_applications', false)
            ->assertJsonPath('data.can_delete_applications', false)
            ->assertJsonPath('data.can_manage_users', false);
    }

    public function test_password_confirmation_accepts_current_password_without_redirect()
    {
        $this->actingAs($this->createUser())->post('/api/v1/auth/password/confirm', ['password' => 'password'])
            ->assertNoContent()->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_forgot_password_sends_a_reset_notification()
    {
        Notification::fake();
        $user = $this->createUser();

        $this->postJson('/api/v1/auth/password/email', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_returns_a_japanese_password_reset_error()
    {
        $this->postJson('/api/v1/auth/password/email', ['email' => 'missing@example.com'])
            ->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'このメールアドレスのユーザーが見つかりません。');
    }

    public function test_password_reset_updates_the_password()
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);

        $this->postJson('/api/v1/auth/password/reset', [
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

        $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422)
            ->assertJsonPath('errors.email.0', 'パスワード再設定トークンが無効です。');
    }

    public function test_expired_password_reset_token_does_not_update_password()
    {
        $user = $this->createUser();
        $token = Password::broker()->createToken($user);
        \Illuminate\Support\Facades\DB::table('password_resets')->where('email', $user->email)
            ->update(['created_at' => now()->subHours(2)]);
        $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token, 'email' => $user->email,
            'password' => 'new-password-123', 'password_confirmation' => 'new-password-123',
        ])->assertStatus(422);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    private function createUser()
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ])->fresh();
    }

}
