<?php

namespace Tests\Feature;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_mark_another_user_deleted_and_restore_without_removing_the_row()
    {
        $admin = $this->createUser('admin@example.com', User::ROLE_SUPER_ADMIN);
        $viewer = $this->createUser('viewer@example.com', User::ROLE_VIEWER);
        $this->actingAs($admin);

        $this->patchJson('/admin/api/v1/users/'.$viewer->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertOk()->assertJsonPath('data.status', User::STATUS_DELETED);

        $this->assertSame(2, User::count());
        $this->assertSame(User::STATUS_DELETED, $viewer->fresh()->status);
        $this->getJson('/admin/api/v1/users')->assertJsonPath('data.1.status', User::STATUS_DELETED);

        $this->patchJson('/admin/api/v1/users/'.$viewer->id.'/status', ['status' => User::STATUS_ACTIVE])
            ->assertOk()->assertJsonPath('data.status', User::STATUS_ACTIVE);
        $this->assertSame(2, User::count());
        $this->assertTrue(Hash::check('password', $viewer->fresh()->password));
    }

    public function test_only_super_admin_can_change_status_and_cannot_delete_self_or_protected_admin()
    {
        $admin = $this->createUser('admin@example.com', User::ROLE_SUPER_ADMIN);
        $editor = $this->createUser('editor@example.com', User::ROLE_EDITOR);

        $this->actingAs($editor)
            ->patchJson('/admin/api/v1/users/'.$admin->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patchJson('/admin/api/v1/users/'.$admin->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertStatus(422);

        $this->patchJson('/admin/api/v1/users/'.$editor->id.'/status', ['status' => 'invalid'])
            ->assertStatus(422)->assertJsonValidationErrors('status');
        $this->assertSame(User::STATUS_ACTIVE, $admin->fresh()->status);
    }

    public function test_user_manager_permission_does_not_grant_deletion_and_super_admin_cannot_delete_self()
    {
        $admin = $this->createUser('other@example.com', User::ROLE_SUPER_ADMIN);
        $secondAdmin = $this->createUser('second@example.com', User::ROLE_SUPER_ADMIN);
        $this->actingAs($admin)
            ->patchJson('/admin/api/v1/users/'.$secondAdmin->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertOk();

        $this->actingAs($secondAdmin)->getJson('/admin/api/v1/session')->assertUnauthorized();
        Role::where('code', User::ROLE_EDITOR)->update(['can_manage_users' => true]);
        $manager = $this->createUser('manager@example.com', User::ROLE_EDITOR);
        $this->actingAs($manager)
            ->patchJson('/admin/api/v1/users/'.$admin->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertForbidden();
        $this->actingAs($admin)
            ->patchJson('/admin/api/v1/users/'.$admin->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertStatus(422);
    }

    public function test_deleted_user_cannot_log_in_or_use_an_existing_session()
    {
        $viewer = $this->createUser('viewer@example.com', User::ROLE_VIEWER);
        $this->actingAs($viewer);
        $viewer->update(['status' => User::STATUS_DELETED]);

        $this->getJson('/admin/api/v1/session')->assertUnauthorized();
        $this->assertGuest();
        $this->post('/login', ['email' => $viewer->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_deleted_user_cannot_request_or_use_password_reset()
    {
        Notification::fake();
        $viewer = $this->createUser('viewer@example.com', User::ROLE_VIEWER);
        $token = Password::broker()->createToken($viewer);
        $viewer->update(['status' => User::STATUS_DELETED]);

        $this->postJson('/password/email', ['email' => $viewer->email])->assertStatus(422);
        Notification::assertNothingSent();
        $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $viewer->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertStatus(422);
        $this->assertTrue(Hash::check('password', $viewer->fresh()->password));
    }

    private function createUser($email, $role)
    {
        return User::create([
            'name' => '利用者',
            'email' => $email,
            'password' => Hash::make('password'),
            'role_id' => Role::where('code', $role)->value('id'),
        ])->fresh();
    }
}
