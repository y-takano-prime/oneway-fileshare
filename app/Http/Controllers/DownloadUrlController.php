<?php

namespace App\Http\Controllers;

use App\Mail\FileShareNotificationMail;
use App\Models\DownloadUrl;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DownloadUrlController extends Controller
{
    public function index(Request $request)
    {
        $query = DownloadUrl::withTrashed()->with('sharedFile', 'user');

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
            'all'         => (clone $query)->whereNull('deleted_at')->count(),
            'wait'        => (clone $query)->whereNull('deleted_at')->where('expires_at', '>', now())->where('download_count', 0)->count(),
            'done'        => (clone $query)->whereNull('deleted_at')->where('download_count', '>', 0)->count(),
            'expired'     => (clone $query)->whereNull('deleted_at')->where('expires_at', '<=', now())->count(),
            'invalidated' => (clone $query)->whereNotNull('deleted_at')->count(),
        ];

        $categoryCounts = [
            'all'         => (clone $query)->whereNull('deleted_at')->count(),
            'business'    => (clone $query)->whereNull('deleted_at')->where('category', 'business')->count(),
            'recruitment' => (clone $query)->whereNull('deleted_at')->where('category', 'recruitment')->count(),
            'other'       => (clone $query)->whereNull('deleted_at')->where('category', 'other')->count(),
        ];

        // 状態フィルター
        $status = $request->input('status', 'all');
        if ($status === 'invalidated') {
            $query->whereNotNull('deleted_at');
        } elseif ($status === 'wait') {
            $query->whereNull('deleted_at')->where('expires_at', '>', now())->where('download_count', 0);
        } elseif ($status === 'done') {
            $query->whereNull('deleted_at')->where('download_count', '>', 0);
        } elseif ($status === 'expired') {
            $query->whereNull('deleted_at')->where('expires_at', '<=', now());
        } else {
            $query->whereNull('deleted_at');
        }

        // 属性フィルター
        $category = $request->input('category', 'all');
        if (in_array($category, ['business', 'recruitment', 'other'])) {
            $query->where('category', $category);
        }

        // ソート
        $allowedSorts = ['created_at', 'expires_at', 'download_count', 'recipient_name', 'category'];
        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $urls = $query->orderBy($sort, $dir)->paginate(20)->withQueryString();

        $staffQ = $request->input('staff_q', '');

        return view('urls.index', compact('urls', 'status', 'category', 'sort', 'dir', 'counts', 'categoryCounts', 'staffQ'));
    }

    public function edit(DownloadUrl $url)
    {
        $this->authorizeOwner($url);

        return view('urls.edit', ['url' => $url]);
    }

    public function update(Request $request, DownloadUrl $url)
    {
        $this->authorizeOwner($url);

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

        return view('urls.create');
    }

    public function storeStep1(Request $request)
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

        $validated['notify_on_download'] = $request->boolean('notify_on_download');

        session(['create_recipient' => $validated]);

        return redirect()->route('urls.create_step2');
    }

    public function createStep2()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        if (!session('create_recipient')) {
            return redirect()->route('urls.create');
        }

        $files = SharedFile::query()->where('user_id', Auth::id())->latest()->get();

        return view('urls.create_step2', ['files' => $files]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'URL発行は担当者のみ操作できます');
        }

        $recipient = session('create_recipient');
        if (!$recipient) {
            return redirect()->route('urls.create');
        }

        if ($request->hasFile('upload_file')) {
            $validated = $request->validate([
                'upload_file' => [
                    'required',
                    'file',
                    'max:102400',
                    'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,csv,txt',
                ],
            ]);

            $file = $validated['upload_file'];
            $storedName = Str::uuid()->toString() . ($file->getClientOriginalExtension() ? '.' . $file->getClientOriginalExtension() : '');
            $path = $file->storeAs('uploads/' . Auth::id(), $storedName);

            $sharedFile = SharedFile::create([
                'user_id'       => Auth::id(),
                'original_name' => $file->getClientOriginalName(),
                'stored_path'   => $path,
                'file_size'     => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
                'category'      => null,
            ]);

            $fileId = $sharedFile->id;
        } else {
            $request->validate([
                'shared_file_id' => [
                    'required',
                    Rule::exists('shared_files', 'id')->where('user_id', Auth::id()),
                ],
            ], [
                'shared_file_id.required' => 'ファイルをアップロードするか、既存のファイルを選択してください。',
            ]);

            $fileId = $request->shared_file_id;
        }

        $url = DownloadUrl::create(array_merge($recipient, [
            'shared_file_id' => $fileId,
            'user_id'        => Auth::id(),
            'token'          => Str::random(64),
            'download_count' => 0,
        ]));

        session()->forget('create_recipient');

        return redirect()->route('urls.complete', $url);
    }

    private function buildMailBody(DownloadUrl $url): string
    {
        $greeting = [];
        if ($url->company_name)    $greeting[] = $url->company_name;
        if ($url->recipient_title) $greeting[] = $url->recipient_title;
        if ($url->company_name || $url->recipient_title) $greeting[] = '';
        $greeting[] = $url->recipient_name . ' 様';

        return implode("\n", array_merge(
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

    private function buildMailText(DownloadUrl $url): string
    {
        return implode("\n", ['件名：ファイルのご案内', '', $this->buildMailBody($url)]);
    }

    public function sendMail(DownloadUrl $url)
    {
        $this->authorizeOwner($url);

        $url->load(['sharedFile', 'user']);

        try {
            Mail::to($url->recipient_email)->send(new FileShareNotificationMail(
                $this->buildMailBody($url),
                optional($url->user)->email,
                optional($url->user)->name
            ));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'メールの送信に失敗しました。しばらく時間をおいて再度お試しください。');
        }

        return back()->with('success', '相手先にメールを送信しました。');
    }

    public function complete(DownloadUrl $url)
    {
        $url->load(['sharedFile', 'user']);
        $mailText = $this->buildMailText($url);
        return view('urls.complete', ['url' => $url, 'mailText' => $mailText]);
    }

    public function show(DownloadUrl $url)
    {
        $this->authorizeOwner($url);

        $url->load('sharedFile');

        $accessLogs = $url->accessLogs()->latest()->paginate(20)->withQueryString();

        $mailText = $this->buildMailText($url);

        return view('urls.show', [
            'url' => $url,
            'mailText' => $mailText,
            'accessLogs' => $accessLogs,
        ]);
    }

    public function destroy(DownloadUrl $url)
    {
        $this->authorizeOwner($url);

        $url->delete();

        return redirect()->route('urls.index')->with('success', 'URLを無効化しました');
    }

    private function authorizeOwner(DownloadUrl $url): void
    {
        if (Auth::user()->role !== 'admin' && $url->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
