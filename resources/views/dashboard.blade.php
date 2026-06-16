@extends('layouts.app')

@section('content')
    <h2 class="mb-3">ダッシュボード</h2>
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">有効なURL</div>
                    <div class="h1">{{ $validCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">期限切れのURL</div>
                    <div class="h1">{{ $expiredCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="subheader">総ダウンロード数</div>
                    <div class="h1">{{ $totalDownloads }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">直近のダウンロードURL</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>ファイル名</th>
                        <th>相手先</th>
                        <th>有効期限</th>
                        <th>DL数</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentUrls as $url)
                        <tr>
                            <td>{{ $url->sharedFile->original_name ?? '-' }}</td>
                            <td>{{ $url->recipient_email }}</td>
                            <td>{{ $url->expires_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
