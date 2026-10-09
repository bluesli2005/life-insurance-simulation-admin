<?php

use App\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentAdminSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing']) || User::query()->exists()) {
            return;
        }

        User::create([
            'name' => '管理者',
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role_id' => Role::where('code', User::ROLE_SUPER_ADMIN)->value('id'),
        ]);
    }
}
