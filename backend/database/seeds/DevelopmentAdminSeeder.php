<?php

use App\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/** 開発・テスト用の初期管理者を作成する。 */
class DevelopmentAdminSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing']) || User::query()->exists()) {
            return;
        }

        User::create([
            'name' => '管理者',
            'email' => config('auth.initial_admin.email'),
            'password' => Hash::make(config('auth.initial_admin.password')),
            'role_id' => Role::where('code', User::ROLE_SUPER_ADMIN)->value('id'),
        ]);
    }
}
