<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                DevelopmentAdminSeeder::class,
                SimulationApplicationSeeder::class,
            ]);
        }
    }
}
