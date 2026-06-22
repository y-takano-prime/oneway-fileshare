<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', ['settings' => $this->loadSettings()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'cleanup_grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'notify_before_delete' => ['boolean'],
            'storage_warning_threshold' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $settings = [
            'cleanup_grace_days' => $validated['cleanup_grace_days'],
            'notify_before_delete' => $request->boolean('notify_before_delete'),
            'storage_warning_threshold' => $validated['storage_warning_threshold'],
        ];

        Storage::put('settings.json', json_encode($settings, JSON_PRETTY_PRINT));

        return redirect()->route('admin.settings.index')->with('success', '設定を保存しました');
    }

    private function loadSettings()
    {
        $defaults = [
            'cleanup_grace_days' => 7,
            'notify_before_delete' => false,
            'storage_warning_threshold' => 80,
        ];

        if (!Storage::exists('settings.json')) {
            return $defaults;
        }

        $saved = json_decode(Storage::get('settings.json'), true);

        return array_merge($defaults, is_array($saved) ? $saved : []);
    }
}
