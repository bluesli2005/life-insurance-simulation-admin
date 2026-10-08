<?php

use App\Models\SimulationApplication;
use Illuminate\Database\Seeder;

class SimulationApplicationSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach (SimulationApplication::STATUSES as $statusIndex => $status) {
            for ($itemIndex = 1; $itemIndex <= 24; $itemIndex++) {
                $sequence = $statusIndex * 24 + $itemIndex;

                $attributes = factory(SimulationApplication::class)->make([
                    'application_number' => sprintf('SIM-2026-%04d', $sequence),
                    'status' => $status,
                ])->getAttributes();

                SimulationApplication::firstOrCreate(
                    ['application_number' => sprintf('SIM-2026-%04d', $sequence)],
                    $attributes
                );
            }
        }
    }
}
