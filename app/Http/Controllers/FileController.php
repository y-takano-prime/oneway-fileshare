<?php

namespace App\Http\Controllers;

use App\Models\DeletedFilesLog;
use App\Models\DownloadUrl;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
