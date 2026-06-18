<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedFile;

class StorageController extends Controller
{
    public function index()
    {
        $totalSize = SharedFile::sum('file_size');
        $storageCapMb = config('fileshare.storage_cap_mb');
        $storageUsedMb = round($totalSize / 1024 / 1024, 1);
        $storagePercent = min(100, round($storageUsedMb / $storageCapMb * 100));

        $byUser = SharedFile::select('user_id')
            ->selectRaw('SUM(file_size) as total_size')
            ->selectRaw('COUNT(*) as file_count')
            ->with('user')
            ->groupBy('user_id')
            ->orderByDesc('total_size')
            ->get();

        $largestFiles = SharedFile::with('user')
            ->orderByDesc('file_size')
            ->take(20)
            ->get();

        return view('admin.storage.index', [
            'totalSize' => $totalSize,
            'storageUsedMb' => $storageUsedMb,
            'storageCapMb' => $storageCapMb,
            'storagePercent' => $storagePercent,
            'byUser' => $byUser,
            'largestFiles' => $largestFiles,
        ]);
    }
}
