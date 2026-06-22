<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        View::share('appVersion', $this->resolveAppVersion());
    }

    /**
     * コミット数（GitHubへのpush履歴）からビルド番号を算出する。
     * gitが使えない環境（本番サーバー等）ではバージョン非表示にフォールバックする。
     */
    private function resolveAppVersion(): ?string
    {
        // Apacheサービスのプロセス環境にはPATHが通っていない場合があるため、
        // 候補のgit実行ファイルを順に試す（本番環境がLinuxの場合は素の"git"で解決する）。
        $candidates = ['git', 'C:\\Program Files\\Git\\cmd\\git.exe'];

        $path = base_path();

        foreach ($candidates as $git) {
            try {
                // Apacheの実行ユーザーとリポジトリ所有者が異なる場合、gitの
                // dubious ownership対策でブロックされるため、コマンド単位で例外を渡す
                // （グローバルなgit設定は変更しない）。
                $cmd = escapeshellarg($git)
                    . ' -c ' . escapeshellarg('safe.directory=' . $path)
                    . ' -C ' . escapeshellarg($path)
                    . ' rev-list --count HEAD 2>&1';
                $count = trim(shell_exec($cmd) ?? '');

                if (ctype_digit($count)) {
                    return "v1.0.{$count}";
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }
}
