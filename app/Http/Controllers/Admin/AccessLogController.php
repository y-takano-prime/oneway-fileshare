<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;

class AccessLogController extends Controller
{
    public function index()
    {
        $logs = AccessLog::with('downloadUrl.sharedFile', 'downloadUrl.user')->latest()->paginate(20);

        return view('admin.logs.index', ['logs' => $logs]);
    }
}
