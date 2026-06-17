<?php

namespace Database\Seeders;

use App\Models\DownloadUrl;
use App\Models\SharedFile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        // 既存のダミーデータをクリア（重複防止）
        $dummyEmails = ['tantosha-a@example.com', 'tantosha-b@example.com'];
        foreach (User::whereIn('email', $dummyEmails)->get() as $u) {
            DownloadUrl::where('user_id', $u->id)->forceDelete();
            SharedFile::where('user_id', $u->id)->delete();
        }

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

        // ---- 担当者A のファイル ----

        // 企業向けファイル
        $invoice   = SharedFile::create(['user_id' => $userA->id, 'original_name' => '請求書_2026年5月.pdf',       'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 184320,  'mime_type' => 'application/pdf',      'category' => 'business']);
        $proposal  = SharedFile::create(['user_id' => $userA->id, 'original_name' => '提案書_2026Q2.pdf',          'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 2621440, 'mime_type' => 'application/pdf',      'category' => 'business']);
        $spec      = SharedFile::create(['user_id' => $userA->id, 'original_name' => '仕様書_v3.2.xlsx',           'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 1153434, 'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'category' => 'business']);
        $contract  = SharedFile::create(['user_id' => $userA->id, 'original_name' => '契約書_甲野商事.docx',       'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 460800,  'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'category' => 'business']);

        // 採用向けファイル
        $passDoc   = SharedFile::create(['user_id' => $userA->id, 'original_name' => '書類選考通過のご連絡.pdf',   'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 215040,  'mime_type' => 'application/pdf', 'category' => 'recruitment']);
        $offerDoc  = SharedFile::create(['user_id' => $userA->id, 'original_name' => '採用通知書.pdf',             'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 143360,  'mime_type' => 'application/pdf', 'category' => 'recruitment']);
        $rejectDoc = SharedFile::create(['user_id' => $userA->id, 'original_name' => '選考結果のご連絡.pdf',       'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 133120,  'mime_type' => 'application/pdf', 'category' => 'recruitment']);
        $onboard   = SharedFile::create(['user_id' => $userA->id, 'original_name' => '入社手続き書類一式.pdf',     'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 512000,  'mime_type' => 'application/pdf', 'category' => 'recruitment']);

        // ---- 担当者B のファイル ----
        $estimate  = SharedFile::create(['user_id' => $userB->id, 'original_name' => '見積書_2026年6月.pdf',       'stored_path' => 'files/dummy/'.Str::random(40), 'file_size' => 307200,  'mime_type' => 'application/pdf', 'category' => 'business']);

        // ---- URL発行データ ----
        $urls = [
            // 企業宛て：有効・未DL
            [
                'shared_file_id'     => $invoice->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '甲野商事株式会社 経理部 田中様',
                'recipient_email'    => 'keiri@kono-shoji.co.jp',
                'expires_at'         => now()->addDays(7),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => true,
            ],
            // 企業宛て：有効・DL済み
            [
                'shared_file_id'     => $proposal->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '乙山物産株式会社 営業部 鈴木様',
                'recipient_email'    => 'eigyo@otoyama-bussan.jp',
                'expires_at'         => now()->addDays(14),
                'download_limit'     => 3,
                'download_count'     => 1,
                'notify_on_download' => true,
            ],
            // 企業宛て：期限切れ
            [
                'shared_file_id'     => $spec->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '丙田建設株式会社 工事部 佐藤様',
                'recipient_email'    => 'koji@heitakensetsu.co.jp',
                'expires_at'         => now()->subDays(5),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => false,
            ],
            // 企業宛て：有効・未DL（DL上限あり）
            [
                'shared_file_id'     => $contract->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '甲野商事株式会社 法務部 山本様',
                'recipient_email'    => 'legal@kono-shoji.co.jp',
                'expires_at'         => now()->addDays(3),
                'download_limit'     => 1,
                'download_count'     => 0,
                'notify_on_download' => true,
            ],
            // 求職者宛て：有効・DL済み
            [
                'shared_file_id'     => $passDoc->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '山田 花子',
                'recipient_email'    => 'yamada.hanako@gmail.com',
                'expires_at'         => now()->addDays(5),
                'download_limit'     => null,
                'download_count'     => 1,
                'notify_on_download' => false,
            ],
            // 求職者宛て：有効・未DL
            [
                'shared_file_id'     => $offerDoc->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '佐々木 健太',
                'recipient_email'    => 'sasaki.kenta@outlook.jp',
                'expires_at'         => now()->addDays(10),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => true,
            ],
            // 求職者宛て：期限切れ
            [
                'shared_file_id'     => $rejectDoc->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '中村 美咲',
                'recipient_email'    => 'nakamura.misaki@yahoo.co.jp',
                'expires_at'         => now()->subDays(3),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => false,
            ],
            // 求職者宛て：有効・未DL
            [
                'shared_file_id'     => $onboard->id,
                'user_id'            => $userA->id,
                'recipient_name'     => '渡辺 雄介',
                'recipient_email'    => 'watanabe.yusuke@gmail.com',
                'expires_at'         => now()->addDays(7),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => true,
            ],
            // 担当者B分
            [
                'shared_file_id'     => $estimate->id,
                'user_id'            => $userB->id,
                'recipient_name'     => '丁商事株式会社 購買部 小林様',
                'recipient_email'    => 'purchase@tei-shoji.co.jp',
                'expires_at'         => now()->addDays(5),
                'download_limit'     => null,
                'download_count'     => 0,
                'notify_on_download' => true,
            ],
        ];

        foreach ($urls as $u) {
            DownloadUrl::create(array_merge($u, ['token' => Str::random(64)]));
        }

        $this->command->info('DummyDataSeeder 完了');
        $this->command->info('  担当者A: tantosha-a@example.com / password');
        $this->command->info('  担当者B: tantosha-b@example.com / password');
    }
}
