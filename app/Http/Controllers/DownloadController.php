<?php

namespace App\Http\Controllers;

use App\Mail\DownloadNotificationMail;
use App\Mail\OtpMail;
use App\Models\AccessLog;
use App\Models\AuthCode;
use App\Models\DownloadUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function showPasscode($token)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        $this->log($url, 'access');
        $this->markVerified($token, 'passcode_verified'); // スキップ扱いで自動付与

        return view('download.passcode', ['token' => $token, 'step' => 'email']);
    }

    public function verifyPasscode($token, Request $request)
    {
        return redirect()->route('download.passcode', ['token' => $token]);
    }

    public function verifyEmail($token, Request $request)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'passcode_verified')) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        $rateLimitKey = 'verify-email:' . $token;

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return view('download.passcode', [
                'token' => $token,
                'step' => 'email',
                'error' => '試行回数が多すぎます。しばらく時間をおいて再度お試しください。',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 900);

        if ($request->input('email') !== $url->recipient_email) {
            $this->log($url, 'email_fail');

            return view('download.passcode', [
                'token' => $token,
                'step' => 'email',
                'error' => 'メールアドレスが正しくありません',
            ]);
        }

        $this->log($url, 'email_ok');
        $this->markVerified($token, 'email_verified');

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        AuthCode::create([
            'download_url_id' => $url->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        $senderEmail = optional($url->user)->email;
        $senderName = optional($url->user)->name;

        try {
            Mail::to($url->recipient_email)->send(new OtpMail($code, $url->sharedFile->original_name, $senderEmail, $senderName));
        } catch (\Throwable $e) {
            report($e);

            return view('download.passcode', [
                'token' => $token,
                'step' => 'email',
                'error' => '認証コードの送信に失敗しました。しばらく時間をおいて再度お試しください。',
            ]);
        }

        return view('download.otp', ['token' => $token]);
    }

    public function showOtp($token)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'email_verified')) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        $authCode = $url->authCode;

        if ($authCode && $authCode->lock_until && $authCode->lock_until->isFuture()) {
            return view('download.otp', [
                'token' => $token,
                'locked' => true,
                'lockUntil' => $authCode->lock_until,
            ]);
        }

        return view('download.otp', ['token' => $token]);
    }

    public function verifyOtp($token, Request $request)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'email_verified')) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        // failed_count/lock_until の読み取り→更新を行単位ロックで直列化し、
        // 並列リクエストによる5回ロックアウトの回避を防ぐ
        $result = DB::transaction(function () use ($url, $request) {
            $authCode = AuthCode::where('download_url_id', $url->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$authCode) {
                return ['type' => 'no_code'];
            }

            if ($authCode->lock_until && $authCode->lock_until->isFuture()) {
                return ['type' => 'locked', 'lockUntil' => $authCode->lock_until];
            }

            if ($authCode->expires_at->isPast()) {
                return ['type' => 'code_expired'];
            }

            if (!hash_equals($authCode->code, (string) $request->input('code'))) {
                $authCode->failed_count += 1;

                if ($authCode->failed_count >= 5) {
                    $authCode->lock_until = now()->addMinutes(30);
                }

                $authCode->save();

                return ['type' => 'wrong_code', 'remaining' => max(0, 5 - $authCode->failed_count)];
            }

            $authCode->used_at = now();
            $authCode->save();

            return ['type' => 'success'];
        });

        switch ($result['type']) {
            case 'no_code':
                return redirect()->route('download.passcode', ['token' => $token]);

            case 'locked':
                return view('download.otp', [
                    'token' => $token,
                    'locked' => true,
                    'lockUntil' => $result['lockUntil'],
                ]);

            case 'code_expired':
                return view('download.otp', [
                    'token' => $token,
                    'error' => 'コードの有効期限が切れました。再度URLにアクセスしてください',
                ]);

            case 'wrong_code':
                $this->log($url, 'otp_fail');

                return view('download.otp', [
                    'token' => $token,
                    'error' => 'コードが正しくありません',
                    'remaining' => $result['remaining'],
                ]);
        }

        $this->log($url, 'otp_ok');
        $this->markVerified($token, 'otp_verified');

        $deletionDate = $url->expires_at->copy()->addDays($this->graceDays());

        return view('download.complete', ['token' => $token, 'url' => $url, 'deletionDate' => $deletionDate]);
    }

    private function graceDays(): int
    {
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true);

            return $settings['cleanup_grace_days'] ?? 7;
        }

        return 7;
    }

    public function download($token)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'otp_verified', 10)) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        // download_count の判定→加算を行単位ロックで直列化し、並列リクエストによる
        // ダウンロード回数上限のバイパスを防ぐ
        $allowed = DB::transaction(function () use ($url) {
            $locked = DownloadUrl::where('id', $url->id)->lockForUpdate()->first();

            if ($locked->download_limit !== null && $locked->download_count >= $locked->download_limit) {
                return false;
            }

            $locked->increment('download_count');

            return true;
        });

        if (!$allowed) {
            return view('download.error', ['reason' => 'limit']);
        }

        $this->log($url, 'download');

        if ($url->notify_on_download) {
            try {
                Mail::to($url->user->email)->send(new DownloadNotificationMail($url, request()->ip()));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return Storage::download($url->sharedFile->stored_path, $url->sharedFile->original_name);
    }

    private function findUrl($token)
    {
        return DownloadUrl::withTrashed()->with('user', 'sharedFile')->where('token', $token)->first();
    }

    private function checkExpiry(?DownloadUrl $url)
    {
        if (!$url || $url->trashed()) {
            return view('download.error', ['reason' => 'invalid']);
        }

        if ($url->expires_at->isPast()) {
            return view('download.error', ['reason' => 'expired']);
        }

        if ($url->download_limit !== null && $url->download_count >= $url->download_limit) {
            return view('download.error', ['reason' => 'limit']);
        }

        return null;
    }

    private function isVerified($token, $step, $ttlMinutes = null)
    {
        $verifiedAt = session("download_auth_{$token}.{$step}");

        if (!$verifiedAt) {
            return false;
        }

        if ($ttlMinutes !== null && now()->timestamp - $verifiedAt > $ttlMinutes * 60) {
            return false;
        }

        return true;
    }

    private function markVerified($token, $step)
    {
        session(["download_auth_{$token}.{$step}" => now()->timestamp]);
    }

    private function log(DownloadUrl $url, $action)
    {
        AccessLog::create([
            'download_url_id' => $url->id,
            'ip_address' => request()->ip(),
            'action' => $action,
        ]);
    }
}
