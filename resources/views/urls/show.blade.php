@extends('layouts.app')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
    <h2 style="font-size:18px;font-weight:600;color:#001240;margin:0">URL詳細</h2>
    <a href="{{ route('urls.index') }}" class="btn-axon-ghost">← 一覧へ戻る</a>
</div>

{{-- 詳細カード --}}
<div class="axon-card" style="margin-bottom:1rem">
    <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="width:140px;font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">ファイル名</td>
            <td style="font-size:13px;color:#001240;padding:8px 0">{{ $url->sharedFile->original_name }}</td>
        </tr>
        @if($url->company_name)
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">企業名</td>
            <td style="font-size:13px;color:#001240;padding:8px 0">{{ $url->company_name }}</td>
        </tr>
        @endif
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">相手先</td>
            <td style="font-size:13px;color:#001240;padding:8px 0">
                {{ $url->recipient_name }}
                @if($url->recipient_title)<span style="color:#7090CC;font-size:12px;margin-left:6px">{{ $url->recipient_title }}</span>@endif
                <span style="color:#7090CC;margin-left:8px">{{ $url->recipient_email }}</span>
            </td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">有効期限</td>
            <td style="font-size:13px;padding:8px 0">
                {{ $url->expires_at->format('Y-m-d H:i') }}
                @if($url->expires_at->isPast())
                    <span class="badge-expired" style="margin-left:8px">期限切れ</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">DL数</td>
            <td style="font-size:13px;color:#001240;padding:8px 0">{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500">URL</td>
            <td style="padding:8px 0">
                <div style="display:flex;gap:8px;align-items:center">
                    <input type="text" id="download-url" value="{{ route('download.passcode', $url->token) }}" class="axon-input" readonly style="font-size:12px;font-family:monospace">
                    <button type="button" class="btn-axon-outline" style="white-space:nowrap" onclick="copyDownloadUrl()">コピー</button>
                </div>
            </td>
        </tr>
    </table>
    @if($url->memo)
    <tr>
        <td style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;padding:8px 0;font-weight:500;vertical-align:top">備考</td>
        <td style="font-size:13px;color:#001240;padding:8px 0;white-space:pre-wrap">{{ $url->memo }}</td>
    </tr>
    @endif
    <div style="margin-top:12px;padding-top:12px;border-top:1px solid #D4DFF5">
        <a href="{{ route('urls.edit', $url) }}" class="btn-axon-outline" style="font-size:12px;padding:5px 12px">有効期限・上限を編集</a>
    </div>
</div>

{{-- メール文章 --}}
<div class="axon-card" style="margin-bottom:1rem;padding:0;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;font-weight:500;color:#001240">送付メール文章</span>
        <button type="button" class="btn-axon-outline" style="font-size:12px;padding:4px 12px" onclick="copyMailText()">全文をコピー</button>
    </div>
    <div style="padding:1rem">
        <p style="font-size:12px;color:#7090CC;margin:0 0 8px">以下の文章をコピーしてメールに貼り付けてください。</p>
        <textarea id="mail-text" class="axon-input" rows="14" style="font-size:12px;font-family:monospace;resize:vertical">{{ $mailText }}</textarea>
        <div id="copy-feedback" style="color:#0066FF;font-size:12px;margin-top:6px;display:none">コピーしました</div>
    </div>
</div>

{{-- アクセスログ --}}
<div class="axon-card" style="padding:0;overflow:hidden">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF">
        <span style="font-size:13px;font-weight:500;color:#001240">アクセスログ</span>
    </div>
    <table class="axon-table">
        <thead>
            <tr>
                <th>日時</th>
                <th>IPアドレス</th>
                <th>アクション</th>
            </tr>
        </thead>
        <tbody>
            @forelse($url->accessLogs as $log)
            <tr>
                <td style="font-size:12px">{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                <td style="font-size:12px;color:#7090CC">{{ $log->ip_address }}</td>
                <td style="font-size:12px">{{ $log->action }}</td>
            </tr>
            @empty
            <tr><td colspan="3" style="text-align:center;color:#7090CC;padding:1.5rem">ログがありません</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('scripts')
<script>
function copyDownloadUrl() {
    const input = document.getElementById('download-url');
    input.select();
    navigator.clipboard ? navigator.clipboard.writeText(input.value) : document.execCommand('copy');
}
function copyMailText() {
    const ta = document.getElementById('mail-text');
    ta.select();
    const fb = document.getElementById('copy-feedback');
    const copy = () => { fb.style.display = 'block'; setTimeout(() => fb.style.display = 'none', 2000); };
    navigator.clipboard ? navigator.clipboard.writeText(ta.value).then(copy) : (document.execCommand('copy'), copy());
}
</script>
@endsection
