<?php

namespace Database\Seeders;

use App\Models\DownloadUrl;
use App\Models\SharedFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // download_urls を全件リセット（紐づくアクセスログ・認証コードも削除）。
        // shared_files（アップロード済みファイル自体）は変更しない。
        DB::table('access_logs')->delete();
        DB::table('auth_codes')->delete();
        DB::table('download_urls')->delete();

        // 担当者ユーザー
        $userA = User::firstOrCreate(
            ['email' => 'tantosha-a@example.com'],
            [
                'name'      => '田中 一郎',
                'password'  => Hash::make('password'),
                'role'      => 'staff',
                'is_active' => true,
            ]
        );

        $userB = User::firstOrCreate(
            ['email' => 'tantosha-b@example.com'],
            [
                'name'      => '鈴木 花子',
                'password'  => Hash::make('password'),
                'role'      => 'staff',
                'is_active' => true,
            ]
        );

        // 既存のアップロード済みファイルを再利用（新規作成しない）
        $file = fn (int $userId, string $name) => SharedFile::where('user_id', $userId)->where('original_name', $name)->first();

        $invoice   = $file($userA->id, '請求書_2026年5月.pdf');
        $proposal  = $file($userA->id, '提案書_2026Q2.pdf');
        $spec      = $file($userA->id, '仕様書_v3.2.xlsx');
        $contract  = $file($userA->id, '契約書_甲野商事.docx');
        $passDoc   = $file($userA->id, '書類選考通過のご連絡.pdf');
        $offerDoc  = $file($userA->id, '採用通知書.pdf');
        $rejectDoc = $file($userA->id, '選考結果のご連絡.pdf');
        $onboard   = $file($userA->id, '入社手続き書類一式.pdf');
        $otherDoc  = $file($userA->id, 'testfile.txt');
        $estimate  = $file($userB->id, '見積書_2026年6月.pdf');

        // ---- URL発行データ（現行フォーマット：category必須、company_name/recipient_title/memoを使用） ----
        $urls = [
            // 取引先・有効・未DL
            [
                'shared_file_id'  => $invoice->id,
                'user_id'         => $userA->id,
                'category'        => 'business',
                'company_name'    => '甲野商事株式会社',
                'recipient_title' => '経理部',
                'recipient_name'  => '田中 太郎',
                'recipient_email' => 'keiri@kono-shoji.co.jp',
                'expires_at'      => now()->addDays(7),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => true,
                'memo'            => '5月分の請求書です。月末までにご確認をお願いします。',
            ],
            // 取引先・有効・DL済み（DL上限に到達）
            [
                'shared_file_id'  => $proposal->id,
                'user_id'         => $userA->id,
                'category'        => 'business',
                'company_name'    => '乙山物産株式会社',
                'recipient_title' => '営業部',
                'recipient_name'  => '鈴木 一郎',
                'recipient_email' => 'eigyo@otoyama-bussan.jp',
                'expires_at'      => now()->addDays(14),
                'download_limit'  => 3,
                'download_count'  => 3,
                'notify_on_download' => true,
                'memo'            => null,
            ],
            // 取引先・期限切れ
            [
                'shared_file_id'  => $spec->id,
                'user_id'         => $userA->id,
                'category'        => 'business',
                'company_name'    => '丙田建設株式会社',
                'recipient_title' => '工事部',
                'recipient_name'  => '佐藤 次郎',
                'recipient_email' => 'koji@heitakensetsu.co.jp',
                'expires_at'      => now()->subDays(5),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => false,
                'memo'            => null,
            ],
            // 取引先・有効・未DL（DL上限あり・未到達）
            [
                'shared_file_id'  => $contract->id,
                'user_id'         => $userA->id,
                'category'        => 'business',
                'company_name'    => '甲野商事株式会社',
                'recipient_title' => '法務部',
                'recipient_name'  => '山本 三郎',
                'recipient_email' => 'legal@kono-shoji.co.jp',
                'expires_at'      => now()->addDays(3),
                'download_limit'  => 1,
                'download_count'  => 0,
                'notify_on_download' => true,
                'memo'            => '契約書の最終版です。捺印後の返送先は別途ご連絡します。',
            ],
            // 採用・有効・DL済み（企業名・役職部署は採用カテゴリのため非表示＝null）
            [
                'shared_file_id'  => $passDoc->id,
                'user_id'         => $userA->id,
                'category'        => 'recruitment',
                'company_name'    => null,
                'recipient_title' => null,
                'recipient_name'  => '山田 花子',
                'recipient_email' => 'yamada.hanako@gmail.com',
                'expires_at'      => now()->addDays(5),
                'download_limit'  => null,
                'download_count'  => 1,
                'notify_on_download' => false,
                'memo'            => null,
            ],
            // 採用・有効・未DL
            [
                'shared_file_id'  => $offerDoc->id,
                'user_id'         => $userA->id,
                'category'        => 'recruitment',
                'company_name'    => null,
                'recipient_title' => null,
                'recipient_name'  => '佐々木 健太',
                'recipient_email' => 'sasaki.kenta@outlook.jp',
                'expires_at'      => now()->addDays(10),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => true,
                'memo'            => null,
            ],
            // 採用・期限切れ
            [
                'shared_file_id'  => $rejectDoc->id,
                'user_id'         => $userA->id,
                'category'        => 'recruitment',
                'company_name'    => null,
                'recipient_title' => null,
                'recipient_name'  => '中村 美咲',
                'recipient_email' => 'nakamura.misaki@yahoo.co.jp',
                'expires_at'      => now()->subDays(3),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => false,
                'memo'            => null,
            ],
            // 採用・有効・未DL
            [
                'shared_file_id'  => $onboard->id,
                'user_id'         => $userA->id,
                'category'        => 'recruitment',
                'company_name'    => null,
                'recipient_title' => null,
                'recipient_name'  => '渡辺 雄介',
                'recipient_email' => 'watanabe.yusuke@gmail.com',
                'expires_at'      => now()->addDays(7),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => true,
                'memo'            => '入社手続きに必要な書類一式です。',
            ],
            // その他・無効化済み（企業名未入力のケースも兼ねる）
            [
                'shared_file_id'  => $otherDoc->id,
                'user_id'         => $userA->id,
                'category'        => 'other',
                'company_name'    => null,
                'recipient_title' => null,
                'recipient_name'  => '高橋 健',
                'recipient_email' => 'takahashi.ken@example.com',
                'expires_at'      => now()->addDays(10),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => false,
                'memo'            => 'テスト送付用ファイル',
                'invalidate'      => true,
            ],
            // 担当者B分・取引先・有効・未DL
            [
                'shared_file_id'  => $estimate->id,
                'user_id'         => $userB->id,
                'category'        => 'business',
                'company_name'    => '丁商事株式会社',
                'recipient_title' => '購買部',
                'recipient_name'  => '小林 真',
                'recipient_email' => 'purchase@tei-shoji.co.jp',
                'expires_at'      => now()->addDays(5),
                'download_limit'  => null,
                'download_count'  => 0,
                'notify_on_download' => true,
                'memo'            => null,
            ],
        ];

        foreach ($urls as $u) {
            $shouldInvalidate = $u['invalidate'] ?? false;
            unset($u['invalidate']);

            $url = DownloadUrl::create(array_merge($u, ['token' => Str::random(64)]));

            if ($shouldInvalidate) {
                $url->delete();
            }
        }

        $this->command->info('DummyDataSeeder 完了（download_urls を現行フォーマットで再投入）');
        $this->command->info('  担当者A: tantosha-a@example.com / password');
        $this->command->info('  担当者B: tantosha-b@example.com / password');
    }
}
