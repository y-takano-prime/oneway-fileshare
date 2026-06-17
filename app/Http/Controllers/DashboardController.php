<?php

namespace App\Http\Controllers;

use App\Models\DownloadUrl;
use App\Models\SharedFile;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $urls = DownloadUrl::query()
            ->when(Auth::user()->role !== 'admin', fn($q) => $q->where('user_id', Auth::id()))
            ->with('sharedFile', 'user')
            ->latest()
            ->get();

        $totalUrls  = $urls->count();
        $doneCount  = $urls->where('download_count', '>', 0)->count();
        $waitCount  = $urls->where('download_count', 0)->filter(fn($u) => !$u->expires_at->isPast())->count();
        $expCount   = $urls->filter(fn($u) => $u->expires_at->isPast())->count();

        $validCount     = $urls->filter(fn($u) => !$u->expires_at->isPast())->count();
        $expiredCount   = $expCount;
        $totalDownloads = $urls->sum('download_count');
        $recentUrls     = $urls->take(10);

        $totalSize       = SharedFile::when(Auth::user()->role !== 'admin', fn($q) => $q->where('user_id', Auth::id()))->sum('file_size');
        $storageUsedMb   = round($totalSize / 1024 / 1024, 1);
        $storageCapMb    = 1024;
        $storagePercent  = min(100, round($storageUsedMb / $storageCapMb * 100));
        $fileCount       = SharedFile::when(Auth::user()->role !== 'admin', fn($q) => $q->where('user_id', Auth::id()))->count();

        return view('dashboard', compact(
            'totalUrls', 'doneCount', 'waitCount', 'expCount',
            'validCount', 'expiredCount', 'totalDownloads', 'recentUrls',
            'storageUsedMb', 'storageCapMb', 'storagePercent', 'fileCount',
            'totalSize'
        ));
    }
}
