<?php

namespace App\Http\Controllers;

use App\Models\DownloadUrl;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DownloadUrlController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadUrl::query()->with('sharedFile');

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($q = $request->input('q')) {
            $query->where(function ($q2) use ($q) {
                $q2->where('recipient_name', 'like', "%{$q}%")
                   ->orWhere('recipient_email', 'like', "%{$q}%")
                   ->orWhereHas('sharedFile', function ($q3) use ($q) {
                       $q3->where('original_name', 'like', "%{$q}%");
                   });
            });
        }

        $urls = $query->latest()->get();

        return view('urls.index', ['urls' => $urls]);
    }

    public function edit(DownloadUrl $url)
    {
        return view('urls.edit', ['url' => $url]);
    }

    public function update(Request $request, DownloadUrl $url)
    {
        $validated = $request->validate([
            'expires_at' => ['required', 'date', 'after:now'],
            'download_limit' => ['nullable', 'integer', 'min:1', 'max:9999'],
        ]);

        $url->update([
            'expires_at' => $validated['expires_at'],
            'download_limit' => $validated['download_limit'] ?? null,
        ]);

        return redirect()->route('urls.show', $url)->with('success', '更新しました');
    }

    public function create(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $query = SharedFile::query();

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $files = $query->latest()->get();

        return view('urls.create', [
            'files' => $files,
            'selectedFileId' => $request->query('shared_file_id'),
        ]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $validated = $request->validate([
            'shared_file_id' => ['required', 'exists:shared_files,id'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_email' => ['required', 'email', 'max:255'],
            'expires_at' => ['required', 'date', 'after:now'],
            'download_limit' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'notify_on_download' => ['boolean'],
        ]);

        DownloadUrl::create([
            'shared_file_id' => $validated['shared_file_id'],
            'user_id' => Auth::id(),
            'token' => Str::random(64),
            'recipient_name' => $validated['recipient_name'],
            'recipient_email' => $validated['recipient_email'],
            'expires_at' => $validated['expires_at'],
            'download_limit' => $validated['download_limit'] ?? null,
            'download_count' => 0,
            'notify_on_download' => $request->boolean('notify_on_download'),
        ]);

        return redirect()->route('urls.index')->with('success', 'URLを発行しました');
    }

    public function show(DownloadUrl $url)
    {
        $url->load(['sharedFile', 'accessLogs' => function ($query) {
            $query->latest();
        }]);

        $mailText = implode("\n", [
            '件名：ファイルのご案内',
            '',
            ($url->recipient_name ?: $url->recipient_email) . ' 様',
            '',
            'お世話になっております。',
            '以下のURLよりファイルをダウンロードいただけます。',
            '',
            '■ファイル名',
            $url->sharedFile->original_name,
            '',
            '■ダウンロードURL',
            route('download.passcode', $url->token),
            '',
            '■有効期限',
            $url->expires_at->format('Y年m月d日 H:i'),
            '',
            'ダウンロード後は手順に従いメール認証を完了してください。',
            '',
            'よろしくお願いいたします。',
        ]);

        return view('urls.show', [
            'url' => $url,
            'mailText' => $mailText,
        ]);
    }

    public function destroy(DownloadUrl $url)
    {
        $url->delete();

        return redirect()->route('urls.index')->with('success', 'URLを無効化しました');
    }
}
