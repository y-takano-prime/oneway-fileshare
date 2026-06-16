@extends('layouts.app')

@section('content')
    <h2 class="mb-3">URL詳細</h2>

    <div class="card mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-3">ファイル名</dt>
                <dd class="col-9">{{ $url->sharedFile->original_name }}</dd>
                <dt class="col-3">相手先</dt>
                <dd class="col-9">{{ $url->recipient_email }}</dd>
                <dt class="col-3">有効期限</dt>
                <dd class="col-9">{{ $url->expires_at->format('Y-m-d H:i') }}</dd>
                <dt class="col-3">ダウンロード数</dt>
                <dd class="col-9">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</dd>
                <dt class="col-3">URL</dt>
                <dd class="col-9">
                    <div class="input-group">
                        <input type="text" class="form-control" id="download-url" value="{{ route('download.passcode', $url->token) }}" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('download-url').value)">コピー</button>
                    </div>
                </dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">アクセスログ</h3>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-vcenter">
                <thead>
                    <tr>
                        <th>日時</th>
                        <th>IPアドレス</th>
                        <th>アクション</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($url->accessLogs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td>{{ $log->action }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">ログがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
