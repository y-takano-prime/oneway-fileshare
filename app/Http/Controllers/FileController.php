<?php

namespace App\Http\Controllers;

use App\Mail\StorageWarningMail;
use App\Models\DeletedFilesLog;
use App\Models\DownloadUrl;
use App\Models\SharedFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = SharedFile::query()
            ->leftJoin('users', 'users.id', '=', 'shared_files.user_id')
            ->select('shared_files.*', 'users.name as uploader_name')
            ->withCount('downloadUrls')
            ->with(['user', 'downloadUrls'])
            ->addSelect(['earliest_expires_at' => DownloadUrl::selectRaw('MIN(expires_at)')
                ->whereColumn('shared_file_id', 'shared_files.id'),
            ]);

        if (Auth::user()->role !== 'admin') {
            $query->where('shared_files.user_id', Auth::id());
        }

        if ($q = $request->input('q')) {
            $query->where('shared_files.original_name', 'like', "%{$q}%");
        }

        $allowedSorts = [
            'original_name'       => 'shared_files.original_name',
            'download_urls_count' => 'download_urls_count',
            'uploader_name'       => 'uploader_name',
            'file_size'           => 'shared_files.file_size',
            'created_at'          => 'shared_files.created_at',
            'earliest_expires_at' => 'earliest_expires_at',
        ];
        $sort = array_key_exists($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $dir  = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $files = $query->orderBy($allowedSorts[$sort], $dir)->paginate(20)->withQueryString();

        $graceDays = 7;
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true);
            $graceDays = $settings['cleanup_grace_days'] ?? 7;
        }

        return view('files.index', ['files' => $files, 'graceDays' => $graceDays, 'sort' => $sort, 'dir' => $dir]);
    }

    public function store(Request $request)
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'ファイルのアップロードは担当者のみ操作できます');
        }

        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:102400',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,csv,txt',
            ],
        ]);

        $batchMb = round(collect($request->file('files'))->sum(fn($f) => $f->getSize()) / 1024 / 1024, 1);
        $maxBatchMb = config('fileshare.max_upload_batch_mb');
        if ($batchMb > $maxBatchMb) {
            return response()->json([
                'message' => "1回のアップロード合計サイズが上限（{$maxBatchMb}MB）を超えています（現在 {$batchMb}MB）。ファイルを分けてアップロードしてください。",
            ], 422);
        }

        $category = in_array($request->input('category'), ['business', 'recruitment', 'other'])
            ? $request->input('category') : null;

        $beforeMb = round(SharedFile::where('user_id', Auth::id())->sum('file_size') / 1024 / 1024, 1);

        foreach ($request->file('files') as $file) {
            $extension = $file->getClientOriginalExtension();
            $storedName = Str::uuid()->toString() . ($extension ? '.' . $extension : '');
            $path = $file->storeAs('uploads/' . Auth::id(), $storedName);

            SharedFile::create([
                'user_id'       => Auth::id(),
                'original_name' => $file->getClientOriginalName(),
                'stored_path'   => $path,
                'file_size'     => $file->getSize(),
                'mime_type'     => $file->getMimeType(),
                'category'      => $category,
            ]);
        }

        $this->notifyStorageWarningIfCrossed(Auth::id(), $beforeMb);

        return redirect()->route('files.index')->with('success', 'ファイルをアップロードしました');
    }

    private function notifyStorageWarningIfCrossed($userId, $beforeMb)
    {
        $capMb = config('fileshare.storage_cap_mb');
        $threshold = 80;
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true);
            $threshold = $settings['storage_warning_threshold'] ?? 80;
        }

        $beforePercent = $beforeMb / $capMb * 100;
        $afterMb = round(SharedFile::where('user_id', $userId)->sum('file_size') / 1024 / 1024, 1);
        $afterPercent = $afterMb / $capMb * 100;

        if ($beforePercent >= $threshold || $afterPercent < $threshold) {
            return;
        }

        $targetUser = User::find($userId);
        $admins = User::where('role', 'admin')->where('is_active', true)->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send(new StorageWarningMail($targetUser, $afterMb, $capMb, round($afterPercent, 1)));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('ストレージ警告メールの送信に失敗しました: ' . $e->getMessage());
            }
        }
    }

    public function destroy(SharedFile $file)
    {
        if (Auth::user()->role !== 'admin' && $file->user_id !== Auth::id()) {
            abort(403);
        }

        Storage::delete($file->stored_path);

        DeletedFilesLog::create([
            'original_name' => $file->original_name,
            'stored_path' => $file->stored_path,
            'deleted_by' => Auth::id(),
        ]);

        foreach ($file->downloadUrls as $url) {
            $url->delete();
        }

        $file->delete();

        return redirect()->route('files.index')->with('success', 'ファイルを削除しました');
    }
}
