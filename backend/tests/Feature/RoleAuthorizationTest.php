<?php

namespace Tests\Feature;

use App\Models\SimulationApplication;
use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_read_but_cannot_write_or_manage_users()
    {
        $viewer = $this->createUser('viewer@example.com', User::ROLE_VIEWER);
        $application = factory(SimulationApplication::class)->create();
        $this->actingAs($viewer);

        $this->getJson('/api/v1/simulation-applications')->assertOk();
        $this->getJson('/api/v1/simulation-applications/'.$application->id)->assertOk();
        $this->postJson('/api/v1/simulation-applications', [])->assertForbidden();
        $this->patchJson('/api/v1/simulation-applications/'.$application->id, [])->assertForbidden();
        $this->deleteJson('/api/v1/simulation-applications/'.$application->id)->assertForbidden();
        $this->getJson('/api/v1/users')->assertForbidden();
        $this->patchJson('/api/v1/users/'.$viewer->id.'/role', ['role' => User::ROLE_SUPER_ADMIN])->assertForbidden();
        $this->assertSame(User::ROLE_VIEWER, $viewer->fresh()->role->code);
    }

    public function test_editor_can_write_but_cannot_delete_or_manage_roles()
    {
        $editor = $this->createUser('editor@example.com', User::ROLE_EDITOR);
        $application = factory(SimulationApplication::class)->create();
        $this->actingAs($editor);

        $this->getJson('/api/v1/simulation-applications')->assertOk();
        $this->patchJson('/api/v1/simulation-applications/'.$application->id, ['notes' => '編集済み'])
            ->assertOk();
        $this->deleteJson('/api/v1/simulation-applications/'.$application->id)->assertForbidden();
        $this->getJson('/api/v1/users')->assertForbidden();
    }

    public function test_super_admin_can_assign_roles_but_not_demote_protected_account_or_last_super_admin()
    {
        $admin = $this->createUser('admin@example.com', User::ROLE_SUPER_ADMIN);
        $viewer = $this->createUser('viewer@example.com', User::ROLE_VIEWER);
        $this->actingAs($admin);

        $this->getJson('/api/v1/users')->assertOk()
            ->assertJsonPath('data.0.email', 'admin@example.com')
            ->assertJsonPath('roles.2.code', User::ROLE_SUPER_ADMIN)
            ->assertJsonPath('roles.2.can_delete_applications', true);
        $this->patchJson('/api/v1/users/'.$viewer->id.'/role', ['role' => User::ROLE_EDITOR])
            ->assertOk()->assertJsonPath('data.role', User::ROLE_EDITOR);
        $this->patchJson('/api/v1/users/'.$viewer->id.'/role', ['role' => 'invalid'])
            ->assertStatus(422)->assertJsonValidationErrors('role');
        $this->patchJson('/api/v1/users/'.$admin->id.'/role', ['role' => User::ROLE_VIEWER])
            ->assertStatus(422);
        $this->assertSame(User::ROLE_SUPER_ADMIN, $admin->fresh()->role->code);
    }

    public function test_non_reserved_super_admin_cannot_demote_the_last_super_admin()
    {
        $admin = $this->createUser('other@example.com', User::ROLE_SUPER_ADMIN);
        $this->actingAs($admin)
            ->patchJson('/api/v1/users/'.$admin->id.'/role', ['role' => User::ROLE_EDITOR])
            ->assertStatus(422);
    }

    public function test_authorization_reads_permission_flags_from_roles_table()
    {
        $editor = $this->createUser('editor@example.com', User::ROLE_EDITOR);
        $application = factory(SimulationApplication::class)->create();
        $this->actingAs($editor);

        $this->deleteJson('/api/v1/simulation-applications/'.$application->id)->assertForbidden();

        Role::where('code', User::ROLE_EDITOR)->update(['can_delete_applications' => true]);
        $editor->unsetRelation('role');

        $this->deleteJson('/api/v1/simulation-applications/'.$application->id)->assertOk();
    }

    private function createUser($email, $role)
    {
        return User::create([
            'name' => '利用者',
            'email' => $email,
            'password' => bcrypt('password'),
            'role_id' => Role::where('code', $role)->value('id'),
        ]);
    }
}
