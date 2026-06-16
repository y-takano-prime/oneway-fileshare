<?php

namespace App\Http\Controllers;

use App\Models\DownloadUrl;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DownloadUrlController extends Controller
{
    public function index()
    {
        $query = DownloadUrl::query()->with('sharedFile');

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $urls = $query->latest()->get();

        return view('urls.index', ['urls' => $urls]);
    }

    public function create()
    {
        $query = SharedFile::query();

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $files = $query->latest()->get();

        return view('urls.create', ['files' => $files]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shared_file_id' => ['required', 'exists:shared_files,id'],
            'passcode' => ['nullable', 'string', 'min:4', 'max:32'],
            'recipient_email' => ['required', 'email', 'max:255'],
            'expires_at' => ['required', 'date', 'after:now'],
            'download_limit' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'notify_on_download' => ['boolean'],
        ]);

        DownloadUrl::create([
            'shared_file_id' => $validated['shared_file_id'],
            'user_id' => Auth::id(),
            'token' => Str::random(64),
            'passcode' => !empty($validated['passcode']) ? Hash::make($validated['passcode']) : null,
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

        return view('urls.show', ['url' => $url]);
    }

    public function destroy(DownloadUrl $url)
    {
        $url->delete();

        return redirect()->route('urls.index')->with('success', 'URLを無効化しました');
    }
}
