<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AccessLog::with('downloadUrl.sharedFile', 'downloadUrl.user');

        $counts = [
            'all'      => (clone $query)->count(),
            'failed'   => (clone $query)->whereIn('action', ['email_fail', 'otp_fail'])->count(),
            'download' => (clone $query)->where('action', 'download')->count(),
        ];

        $status = $request->input('status', 'all');
        if ($status === 'failed') {
            $query->whereIn('action', ['email_fail', 'otp_fail']);
        } elseif ($status === 'download') {
            $query->where('action', 'download');
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.logs.index', compact('logs', 'status', 'counts'));
    }
}
