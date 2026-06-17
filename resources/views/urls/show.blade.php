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
                        <button type="button" class="btn btn-outline-secondary" onclick="copyDownloadUrl()">コピー</button>
                    </div>
                </dd>
            </dl>
            <div class="mt-3">
                <a href="{{ route('urls.edit', $url) }}" class="btn btn-sm btn-outline-secondary">有効期限・上限を編集</a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">送付メール文章</h3>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyMailText()">
                全文をコピー
            </button>
        </div>
        <div class="card-body">
            <div class="mb-2 text-muted" style="font-size: 0.85rem;">
                以下の文章をコピーして、メールシステムに貼り付けてください。
            </div>
            <textarea id="mail-text" class="form-control" rows="14" readonly style="font-size: 0.9rem; white-space: pre; font-family: monospace;">{{ $mailText }}</textarea>
            <div id="copy-feedback" class="mt-2 text-success d-none">コピーしました</div>
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

@section('scripts')
    <script>
        function copyDownloadUrl() {
            const input = document.getElementById('download-url');
            input.select();
            input.setSelectionRange(0, 99999);

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value);
            } else {
                document.execCommand('copy');
            }
        }

        function copyMailText() {
            const textarea = document.getElementById('mail-text');
            textarea.select();
            textarea.setSelectionRange(0, 99999);

            const feedback = document.getElementById('copy-feedback');

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textarea.value).then(() => {
                    feedback.classList.remove('d-none');
                    setTimeout(() => feedback.classList.add('d-none'), 2000);
                });
            } else {
                document.execCommand('copy');
                feedback.classList.remove('d-none');
                setTimeout(() => feedback.classList.add('d-none'), 2000);
            }
        }
    </script>
@endsection
