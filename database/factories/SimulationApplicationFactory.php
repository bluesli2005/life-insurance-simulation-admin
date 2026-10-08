<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\SimulationApplication;
use Faker\Generator as Faker;

$factory->define(SimulationApplication::class, function (Faker $faker) {
    $effectiveDate = $faker->dateTimeBetween('-1 year', '+1 year')->format('Y-m-d');
    $japaneseNotes = [
        '申込書類を受領し、内容を確認しています。',
        '本人確認書類の確認が完了しました。',
        '契約内容について申込者へ説明済みです。',
        '保険料の支払方法を確認しています。',
        '追加書類の提出を依頼しました。',
        '健康状態の告知内容を確認中です。',
        '申込者から契約内容について問い合わせがありました。',
        '被保険者情報の照合が完了しました。',
        '受取人情報に誤りがないことを確認しました。',
        '初回保険料の入金を確認しました。',
        '申込内容について再確認を依頼しています。',
        '契約開始日を申込者と調整中です。',
        '書類に不足があるため、再提出を依頼しました。',
        '審査結果を申込者へ連絡しました。',
        '申込者の希望により、手続きを一時保留しています。',
        '保障内容の説明を行い、了承を得ました。',
        '申込者へ必要事項を案内しました。',
        '契約条件の確認が完了しました。',
        '追加確認事項はありません。',
        '審査に必要な情報がすべてそろいました。',
    ];

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
        'notes' => $faker->boolean(75) ? $faker->randomElement($japaneseNotes) : null,
    ];
});
