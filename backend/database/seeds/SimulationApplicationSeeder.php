<?php

use App\Models\SimulationApplication;
use Illuminate\Database\Seeder;

/** 開発・テスト用のサンプル申込を用意する。 */
class SimulationApplicationSeeder extends Seeder
{
    public function run()
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // 既存のサンプル申込の氏名と備考を日本語に統一する。
        $japaneseNames = [
            '佐藤 花子', '鈴木 太郎', '高橋 美咲', '田中 健太', '伊藤 さくら',
            '渡辺 翔太', '山本 葵', '中村 大輔', '小林 結衣', '加藤 直樹',
            '吉田 愛', '山田 拓海', '佐々木 優奈', '山口 陽菜', '松本 悠真',
            '井上 彩花', '木村 颯太', '林 美月', '清水 蓮', '斎藤 心春',
        ];
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
        $sampleNumbers = array_map(function ($sequence) {
            return sprintf('SIM-2026-%04d', $sequence);
        }, range(1, count(SimulationApplication::STATUSES) * 24));
        $sampleApplications = SimulationApplication::whereIn('application_number', $sampleNumbers)
            ->orderBy('id')
            ->get();

        foreach ($sampleApplications as $index => $application) {
            $application->applicant_name = $japaneseNames[$index % count($japaneseNames)];
            $application->insured_name = $japaneseNames[($index + 7) % count($japaneseNames)];
            $application->beneficiary_name = $japaneseNames[($index + 13) % count($japaneseNames)];
            $application->notes = $japaneseNotes[$index % count($japaneseNotes)];
            $application->save();
        }

        foreach (SimulationApplication::STATUSES as $statusIndex => $status) {
            for ($itemIndex = 1; $itemIndex <= 24; $itemIndex++) {
                $sequence = $statusIndex * 24 + $itemIndex;

                $attributes = factory(SimulationApplication::class)->make([
                    'application_number' => sprintf('SIM-2026-%04d', $sequence),
                    'status' => $status,
                ])->getAttributes();

                // 削除済みの申込番号も既存扱いとし、再実行時の重複作成を防ぐ。
                SimulationApplication::withTrashed()->firstOrCreate(
                    ['application_number' => sprintf('SIM-2026-%04d', $sequence)],
                    $attributes
                );
            }
        }
    }
}
