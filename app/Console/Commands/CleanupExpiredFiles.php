<?php

namespace App\Console\Commands;

use App\Mail\DeleteNotificationMail;
use App\Models\DeletedFilesLog;
use App\Models\DownloadUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredFiles extends Command
{
    protected $signature = 'fileshare:cleanup';

    protected $description = '有効期限切れのダウンロードURL・ファイルを猶予期間後に自動削除する';

    public function handle()
    {
        $settings = $this->loadSettings();
        $graceDays = $settings['cleanup_grace_days'];
        $notifyBeforeDelete = $settings['notify_before_delete'];

        $urls = DownloadUrl::withTrashed()
            ->where('expires_at', '<', now())
            ->get();

        foreach ($urls as $url) {
            $deletionDate = $url->expires_at->copy()->addDays($graceDays);

            if ($notifyBeforeDelete && now()->isSameDay($deletionDate->copy()->subDays(7))) {
                Mail::to($url->user->email)->send(new DeleteNotificationMail($url, $deletionDate));
            }

            if ($deletionDate->isPast()) {
                Storage::delete($url->sharedFile->stored_path);

                DeletedFilesLog::create([
                    'original_name' => $url->sharedFile->original_name,
                    'stored_path' => $url->sharedFile->stored_path,
                    'deleted_by' => $url->user_id,
                ]);

                $url->forceDelete();
            }
        }

        $this->info('クリーンアップ処理が完了しました');
    }

    private function loadSettings()
    {
        $defaults = [
            'passcode_required' => false,
            'cleanup_grace_days' => 7,
            'notify_before_delete' => false,
        ];

        if (!Storage::exists('settings.json')) {
            return $defaults;
        }

        $saved = json_decode(Storage::get('settings.json'), true);

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }
}
