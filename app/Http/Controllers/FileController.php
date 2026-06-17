<?php

namespace App\Http\Controllers;

use App\Models\DeletedFilesLog;
use App\Models\SharedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request)
    {
        $query = SharedFile::withCount('downloadUrls')->with(['user', 'downloadUrls']);

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        if ($q = $request->input('q')) {
            $query->where('original_name', 'like', "%{$q}%");
        }

        $files = $query->latest()->get();

        $graceDays = 7;
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true);
            $graceDays = $settings['cleanup_grace_days'] ?? 7;
        }

        return view('files.index', ['files' => $files, 'graceDays' => $graceDays]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'file',
                'max:102400',
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,csv,txt',
            ],
        ]);

        $category = in_array($request->input('category'), ['business', 'recruitment', 'other'])
            ? $request->input('category') : null;

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

        return redirect()->route('files.index')->with('success', 'ファイルをアップロードしました');
    }

    public function destroy(SharedFile $file)
    {
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
