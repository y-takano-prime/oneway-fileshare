@extends('layouts.app')

@section('content')
<h2 style="font-size:18px;font-weight:600;color:#001240;margin:0 0 1.25rem">新規作成</h2>

{{-- ステップバー --}}
<div class="axon-steps">
    <div class="axon-step">1. ファイル選択</div>
    <div class="axon-step">2. 送付先設定</div>
    <div class="axon-step active">3. メール確認</div>
</div>

<div class="axon-alert-success">URLを発行しました。以下のメール文章を相手先に送付してください。</div>

{{-- 発行情報 --}}
<div class="axon-card" style="margin-bottom:1rem">
    <div style="font-size:11px;color:#7090CC;letter-spacing:.04em;text-transform:uppercase;font-weight:600;margin-bottom:10px">発行情報</div>
    <table style="width:100%;border-collapse:collapse">
        <tr>
            <td style="width:130px;font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">ファイル名</td>
            <td style="font-size:13px;color:#001240;padding:6px 0">{{ $url->sharedFile->original_name }}</td>
        </tr>
        @if($url->company_name)
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">企業名</td>
            <td style="font-size:13px;color:#001240;padding:6px 0">{{ $url->company_name }}</td>
        </tr>
        @endif
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">相手先</td>
            <td style="font-size:13px;color:#001240;padding:6px 0">
                {{ $url->recipient_name }}
                @if($url->recipient_title)<span style="color:#7090CC;font-size:12px;margin-left:6px">{{ $url->recipient_title }}</span>@endif
            </td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">メール</td>
            <td style="font-size:13px;color:#001240;padding:6px 0">{{ $url->recipient_email }}</td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">有効期限</td>
            <td style="font-size:13px;color:#001240;padding:6px 0">{{ $url->expires_at->format('Y年m月d日 H:i') }}</td>
        </tr>
        <tr>
            <td style="font-size:11px;color:#7090CC;letter-spacing:.03em;text-transform:uppercase;padding:6px 0;font-weight:500">DL URL</td>
            <td style="padding:6px 0">
                <div style="display:flex;align-items:center;gap:8px">
                    <span id="dl-url" style="font-size:12px;color:#0066FF;font-family:monospace;word-break:break-all">{{ route('download.passcode', $url->token) }}</span>
                    <button type="button" id="url-copy-btn" onclick="copyDlUrl()" class="btn-axon-outline" style="white-space:nowrap;padding:3px 10px;font-size:11px;flex-shrink:0">コピー</button>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- メール文章 --}}
<div class="axon-card" style="padding:0;overflow:hidden;margin-bottom:1rem">
    <div style="padding:12px 16px;border-bottom:0.5px solid #D0DEFF;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;font-weight:500;color:#001240">メール文章</span>
        <button type="button" id="copy-btn" class="btn-axon-outline" style="font-size:12px;padding:4px 12px">全文をコピー</button>
    </div>
    <div style="padding:1rem">
        <textarea id="mail-text" class="axon-input" rows="18" style="font-size:12px;font-family:monospace;resize:vertical">{{ $mailText }}</textarea>
    </div>
</div>

<div style="display:flex;justify-content:flex-end">
    <a href="{{ route('urls.index') }}" class="btn-axon-ghost">URL管理へ戻る</a>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('copy-btn').addEventListener('click', function() {
    const ta = document.getElementById('mail-text');
    ta.select();
    ta.setSelectionRange(0, 99999);
    document.execCommand('copy');
    this.textContent = 'コピーしました！';
    setTimeout(() => { this.textContent = '全文をコピー'; }, 2000);
});

function copyDlUrl() {
    const url = document.getElementById('dl-url').textContent.trim();
    const btn = document.getElementById('url-copy-btn');
    const ta = document.createElement('textarea');
    ta.value = url;
    ta.style.position = 'fixed';
    ta.style.top = '-9999px';
    document.body.appendChild(ta);
    ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
    btn.textContent = 'コピー済み';
    setTimeout(() => { btn.textContent = 'コピー'; }, 2000);
}
</script>
@endsection
