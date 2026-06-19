<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SharedFile;
use Illuminate\Support\Facades\Storage;

class StorageController extends Controller
{
    public function index()
    {
        $totalSize = SharedFile::sum('file_size');
        $storageCapMb = config('fileshare.storage_cap_mb');
        $storageUsedMb = round($totalSize / 1024 / 1024, 1);
        $storagePercent = min(100, round($storageUsedMb / $storageCapMb * 100));
        $warningThreshold = $this->storageWarningThreshold();

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
            'warningThreshold' => $warningThreshold,
            'byUser' => $byUser,
            'largestFiles' => $largestFiles,
        ]);
    }

    private function storageWarningThreshold()
    {
        if (Storage::exists('settings.json')) {
            $settings = json_decode(Storage::get('settings.json'), true);
            return $settings['storage_warning_threshold'] ?? 80;
        }

        return 80;
    }
}
