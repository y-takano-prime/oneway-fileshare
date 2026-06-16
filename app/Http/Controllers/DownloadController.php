<?php

namespace App\Http\Controllers;

use App\Mail\DownloadNotificationMail;
use App\Mail\OtpMail;
use App\Models\AccessLog;
use App\Models\AuthCode;
use App\Models\DownloadUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function showPasscode($token)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'passcode_verified')) {
            $this->log($url, 'access');

            return view('download.passcode', ['token' => $token, 'step' => 'passcode']);
        }

        return view('download.passcode', ['token' => $token, 'step' => 'email']);
    }

    public function verifyPasscode($token, Request $request)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if ($url->passcode && !Hash::check((string) $request->input('passcode'), $url->passcode)) {
            $this->log($url, 'passcode_fail');

            return view('download.passcode', [
                'token' => $token,
                'step' => 'passcode',
                'error' => 'パスコードが正しくありません',
            ]);
        }

        $this->log($url, 'passcode_ok');
        $this->markVerified($token, 'passcode_verified');

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

        Mail::to($url->recipient_email)->send(new OtpMail($code, $url->sharedFile->original_name));

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

        $authCode = $url->authCode;

        if (!$authCode) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        if ($authCode->lock_until && $authCode->lock_until->isFuture()) {
            return view('download.otp', [
                'token' => $token,
                'locked' => true,
                'lockUntil' => $authCode->lock_until,
            ]);
        }

        if ($authCode->expires_at->isPast()) {
            return view('download.otp', [
                'token' => $token,
                'error' => 'コードの有効期限が切れました。再度URLにアクセスしてください',
            ]);
        }

        if (!hash_equals($authCode->code, (string) $request->input('code'))) {
            $authCode->failed_count += 1;

            if ($authCode->failed_count >= 5) {
                $authCode->lock_until = now()->addMinutes(30);
            }

            $authCode->save();
            $this->log($url, 'otp_fail');

            return view('download.otp', [
                'token' => $token,
                'error' => 'コードが正しくありません',
                'remaining' => max(0, 5 - $authCode->failed_count),
            ]);
        }

        $authCode->used_at = now();
        $authCode->save();

        $this->log($url, 'otp_ok');
        $this->markVerified($token, 'otp_verified');

        return view('download.complete', ['token' => $token, 'url' => $url]);
    }

    public function download($token)
    {
        $url = $this->findUrl($token);

        if ($error = $this->checkExpiry($url)) {
            return $error;
        }

        if (!$this->isVerified($token, 'otp_verified')) {
            return redirect()->route('download.passcode', ['token' => $token]);
        }

        $url->increment('download_count');
        $this->log($url, 'download');

        if ($url->notify_on_download) {
            Mail::to($url->user->email)->send(new DownloadNotificationMail($url, request()->ip()));
        }

        return Storage::download($url->sharedFile->stored_path, $url->sharedFile->original_name);
    }

    private function findUrl($token)
    {
        $url = DownloadUrl::where('token', $token)->first();

        if (!$url) {
            abort(404);
        }

        return $url;
    }

    private function checkExpiry(DownloadUrl $url)
    {
        if ($url->expires_at->isPast()) {
            return view('download.error', ['reason' => 'expired']);
        }

        if ($url->download_limit !== null && $url->download_count >= $url->download_limit) {
            return view('download.error', ['reason' => 'limit']);
        }

        return null;
    }

    private function isVerified($token, $step)
    {
        return (bool) session("download_auth_{$token}.{$step}", false);
    }

    private function markVerified($token, $step)
    {
        session(["download_auth_{$token}.{$step}" => true]);
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
