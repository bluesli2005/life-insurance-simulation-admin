<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevelopmentAdminSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => '管理者',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );
    }
}
