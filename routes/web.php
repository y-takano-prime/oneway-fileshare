<?php

use App\Http\Controllers\Admin\AccessLogController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\DownloadUrlController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ゲスト
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// 認証済み
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('files', FileController::class)->only(['index', 'store', 'destroy']);
    Route::get('urls/create/step2', [DownloadUrlController::class, 'createStep2'])->name('urls.create_step2');
    Route::post('urls/step1', [DownloadUrlController::class, 'storeStep1'])->name('urls.store_step1');
    Route::resource('urls', DownloadUrlController::class)->only(['index', 'create', 'store', 'show', 'destroy', 'edit', 'update']);
    Route::get('urls/{url}/complete', [DownloadUrlController::class, 'complete'])->name('urls.complete');

    // 管理者専用
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('logs', [AccessLogController::class, 'index'])->name('logs.index');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

// 相手先（認証不要）
Route::prefix('d/{token}')->group(function () {
    Route::get('/', [DownloadController::class, 'showPasscode'])->name('download.passcode');
    Route::post('/passcode', [DownloadController::class, 'verifyPasscode'])->name('download.verify-passcode');
    Route::post('/email', [DownloadController::class, 'verifyEmail'])->name('download.verify-email');
    Route::get('/otp', [DownloadController::class, 'showOtp'])->name('download.otp');
    Route::post('/otp', [DownloadController::class, 'verifyOtp'])->name('download.verify-otp');
    Route::get('/download', [DownloadController::class, 'download'])->name('download.file');
});

// トップは /login へリダイレクト
Route::get('/', function () {
    return redirect()->route('login');
});
