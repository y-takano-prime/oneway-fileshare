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
        $query = DownloadUrl::query()->with('sharedFile', 'user');

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        // 検索
        if ($q = $request->input('q')) {
            $query->where(function ($q2) use ($q) {
                $q2->where('recipient_name', 'like', "%{$q}%")
                   ->orWhere('recipient_email', 'like', "%{$q}%")
                   ->orWhereHas('sharedFile', function ($q3) use ($q) {
                       $q3->where('original_name', 'like', "%{$q}%");
                   });
            });
        }

        if ($staffQ = $request->input('staff_q')) {
            $staffQNoSpace = str_replace([' ', '　'], '', $staffQ);
            $query->whereHas('user', function ($q2) use ($staffQ, $staffQNoSpace) {
                $q2->where('name', 'like', "%{$staffQ}%")
                   ->orWhereRaw("REPLACE(REPLACE(name, ' ', ''), '　', '') LIKE ?", ["%{$staffQNoSpace}%"]);
            });
        }

        // タブ用カウント（フィルター適用前）
        $counts = [
            'all'     => (clone $query)->count(),
            'wait'    => (clone $query)->where('expires_at', '>', now())->where('download_count', 0)->count(),
            'done'    => (clone $query)->where('download_count', '>', 0)->count(),
            'expired' => (clone $query)->where('expires_at', '<=', now())->count(),
        ];

        // 状態フィルター
        $status = $request->input('status', 'all');
        if ($status === 'wait') {
            $query->where('expires_at', '>', now())->where('download_count', 0);
        } elseif ($status === 'done') {
            $query->where('download_count', '>', 0);
        } elseif ($status === 'expired') {
            $query->where('expires_at', '<=', now());
        }

        // ソート
        $allowedSorts = ['created_at', 'expires_at', 'download_count', 'recipient_name'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $urls = $query->orderBy($sort, $dir)->get();

        $staffQ = $request->input('staff_q', '');

        return view('urls.index', compact('urls', 'status', 'sort', 'dir', 'counts', 'staffQ'));
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

        $query = SharedFile::query()->where('user_id', Auth::id());
        $files = $query->latest()->get();

        return view('urls.create', ['files' => $files]);
    }

    public function storeStep1(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $request->validate(['shared_file_id' => ['required', 'exists:shared_files,id']]);
        session(['create_file_id' => $request->shared_file_id]);

        return redirect()->route('urls.create_step2');
    }

    public function createStep2()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $fileId = session('create_file_id');
        if (!$fileId) {
            return redirect()->route('urls.create');
        }

        $file = SharedFile::findOrFail($fileId);

        return view('urls.create_step2', ['file' => $file]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $validated = $request->validate([
            'category'           => ['required', 'in:business,recruitment,other'],
            'recipient_name'     => ['required', 'string', 'max:255'],
            'company_name'       => ['nullable', 'string', 'max:255'],
            'recipient_title'    => ['nullable', 'string', 'max:255'],
            'recipient_email'    => ['required', 'email', 'max:255'],
            'expires_at'         => ['required', 'date', 'after:now'],
            'download_limit'     => ['nullable', 'integer', 'min:1', 'max:9999'],
            'notify_on_download' => ['boolean'],
            'memo'               => ['nullable', 'string', 'max:2000'],
        ]);

        $fileId = session('create_file_id');
        if (!$fileId) {
            return redirect()->route('urls.create');
        }

        $url = DownloadUrl::create([
            'shared_file_id'     => $fileId,
            'user_id'            => Auth::id(),
            'category'           => $validated['category'],
            'token'              => Str::random(64),
            'recipient_name'     => $validated['recipient_name'],
            'company_name'       => $validated['company_name'] ?? null,
            'recipient_title'    => $validated['recipient_title'] ?? null,
            'recipient_email'    => $validated['recipient_email'],
            'expires_at'         => $validated['expires_at'],
            'download_limit'     => $validated['download_limit'] ?? null,
            'download_count'     => 0,
            'notify_on_download' => $request->boolean('notify_on_download'),
            'memo'               => $validated['memo'] ?? null,
        ]);

        session()->forget('create_file_id');

        return redirect()->route('urls.complete', $url);
    }

    private function buildMailText(DownloadUrl $url): string
    {
        $greeting = [];
        if ($url->company_name)    $greeting[] = $url->company_name;
        if ($url->recipient_title) $greeting[] = $url->recipient_title;
        if ($url->company_name || $url->recipient_title) $greeting[] = '';
        $greeting[] = $url->recipient_name . ' 様';

        return implode("\n", array_merge(
            ['件名：ファイルのご案内', ''],
            $greeting,
            [
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
            ]
        ));
    }

    public function complete(DownloadUrl $url)
    {
        $url->load('sharedFile');
        $mailText = $this->buildMailText($url);
        return view('urls.complete', ['url' => $url, 'mailText' => $mailText]);
    }

    public function show(DownloadUrl $url)
    {
        $url->load(['sharedFile', 'accessLogs' => function ($query) {
            $query->latest();
        }]);

        $mailText = $this->buildMailText($url);

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
