<?php

use Illuminate\Database\Seeder;

/** 開発・テスト環境向けの初期データ投入をまとめる。 */
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
