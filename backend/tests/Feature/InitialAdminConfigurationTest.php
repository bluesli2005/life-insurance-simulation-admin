<?php

namespace Tests\Feature;

use App\Role;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InitialAdminConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /** @dataProvider initialAdminEmails */
    public function test_configured_initial_admin_is_seeded_and_protected($email)
    {
        config([
            'auth.initial_admin.email' => $email,
            'auth.initial_admin.password' => 'configured-password-123',
        ]);
        $this->seed('DevelopmentAdminSeeder');
        $reserved = User::firstOrFail();
        $this->assertSame($email, $reserved->email);
        $this->assertTrue(Hash::check('configured-password-123', $reserved->password));
        $this->assertSame(User::ROLE_SUPER_ADMIN, $reserved->role->code);

        $other = User::create([
            'name' => '別の管理者', 'email' => 'other@example.com',
            'password' => Hash::make('other-password-123'),
            'role_id' => Role::where('code', User::ROLE_SUPER_ADMIN)->value('id'),
        ])->fresh();
        $this->actingAs($other)
            ->patchJson('/api/v1/users/'.$reserved->id.'/role', ['role' => User::ROLE_EDITOR])
            ->assertStatus(422);
        $this->patchJson('/api/v1/users/'.$reserved->id.'/status', ['status' => User::STATUS_DELETED])
            ->assertStatus(422);

        $this->assertSame(User::ROLE_SUPER_ADMIN, $reserved->fresh()->role->code);
        $this->assertSame(User::STATUS_ACTIVE, $reserved->fresh()->status);
    }

    public function initialAdminEmails(): array
    {
        return ['default' => ['admin@example.com'], 'custom' => ['owner@example.com']];
    }

    public function test_initial_admin_seeder_is_disabled_in_production()
    {
        $this->app['env'] = 'production';
        $this->app->make(\DevelopmentAdminSeeder::class)->run();

        $this->assertSame(0, User::count());
    }
}
