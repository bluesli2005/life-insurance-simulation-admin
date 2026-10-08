<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\SimulationApplication;
use Faker\Generator as Faker;

$factory->define(SimulationApplication::class, function (Faker $faker) {
    $effectiveDate = $faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d');

    return [
        'application_number' => 'SIM-'.$faker->unique()->numerify('##########'),
        'applicant_name' => $faker->name,
        'insured_name' => $faker->name,
        'insured_birth_date' => $faker->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
        'beneficiary_name' => $faker->optional()->name,
        'coverage_amount' => $faker->randomFloat(2, 100000, 100000000),
        'premium_amount' => $faker->randomFloat(2, 1000, 1000000),
        'currency' => 'JPY',
        'status' => $faker->randomElement(SimulationApplication::STATUSES),
        'effective_date' => $effectiveDate,
        'expiry_date' => $faker->boolean(70)
            ? $faker->dateTimeBetween($effectiveDate, '+30 years')->format('Y-m-d')
            : null,
        'notes' => $faker->optional()->realText(100),
    ];
});
