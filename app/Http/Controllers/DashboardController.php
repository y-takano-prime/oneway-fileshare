<?php

namespace App\Http\Controllers;

use App\Models\DownloadUrl;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $query = DownloadUrl::query();

        if (Auth::user()->role !== 'admin') {
            $query->where('user_id', Auth::id());
        }

        $validCount = (clone $query)->valid()->count();
        $expiredCount = (clone $query)->where('expires_at', '<=', now())->count();
        $totalDownloads = (clone $query)->sum('download_count');
        $recentUrls = (clone $query)->with('sharedFile')->latest()->take(10)->get();

        return view('dashboard', [
            'validCount' => $validCount,
            'expiredCount' => $expiredCount,
            'totalDownloads' => $totalDownloads,
            'recentUrls' => $recentUrls,
        ]);
    }
}
