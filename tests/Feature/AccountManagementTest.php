<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_administrator_is_registered_in_the_database_and_can_enter_the_admin_page()
    {
        $this->post('/register', [
            'name' => '新規 管理者',
            'email' => 'new-admin@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
            'role' => User::ROLE_SUPER_ADMIN,
        ])->assertRedirect('/admin/applications');

        $user = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertSame('新規 管理者', $user->name);
        $this->assertSame(User::ROLE_VIEWER, $user->role->code);
        $this->assertTrue(Hash::check('secure-password-123', $user->password));
        $this->assertNotSame('secure-password-123', $user->password);
        $this->assertAuthenticatedAs($user);
        $this->get('/admin/applications')->assertOk();
    }

    public function test_registration_rejects_duplicate_email_and_unconfirmed_password()
    {
        User::create([
            'name' => '管理者',
            'email' => 'existing@example.com',
            'password' => Hash::make('password-123'),
        ]);

        $this->postJson('/register', [
            'name' => '新規 管理者',
            'email' => 'existing@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'different-password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        $this->assertSame(1, User::count());
    }

    public function test_password_change_requires_login_and_current_password()
    {
        $this->postJson('/admin/password', [])->assertUnauthorized();

        $user = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $this->actingAs($user)
            ->postJson('/admin/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', 'パスワードが正しくありません。');

        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
    }

    public function test_password_change_updates_the_hash_and_keeps_the_current_session()
    {
        $user = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $this->actingAs($user);
        session(['auth.password_confirmed_at' => time()]);

        $this->postJson('/admin/password', [
            'current_password' => 'old-password-123',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk()
            ->assertJsonPath('message', 'パスワードを変更しました。');

        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertNotEmpty($user->fresh()->getRememberToken());
        $this->assertNull(session('auth.password_confirmed_at'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_seeder_never_overwrites_an_existing_database_account()
    {
        $this->seed('DevelopmentAdminSeeder');
        $user = User::firstOrFail();
        $this->assertSame(User::ROLE_SUPER_ADMIN, $user->role->code);
        $user->update([
            'email' => 'renamed@example.com',
            'password' => Hash::make('changed-password-123'),
        ]);

        $this->seed('DevelopmentAdminSeeder');

        $this->assertSame(1, User::count());
        $this->assertSame('renamed@example.com', $user->fresh()->email);
        $this->assertTrue(Hash::check('changed-password-123', $user->fresh()->password));
    }
}
