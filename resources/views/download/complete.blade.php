<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>AXON — ダウンロード</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #F5F8FF; font-family: system-ui, -apple-system, sans-serif; margin: 0; }
        .dl-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        .dl-card { background: #fff; border: 0.5px solid #D0DEFF; border-radius: 12px; padding: 2rem 2.5rem; width: 100%; max-width: 420px; }
        .dl-logo { display: flex; align-items: center; gap: 7px; font-size: 15px; font-weight: 600; color: #001240; letter-spacing: -0.02em; margin-bottom: 4px; justify-content: center; }
        .dl-brand-sub { font-size: 11px; color: #7090CC; text-align: center; margin: 0 0 1.75rem; }
        .dl-logo-mark { width: 22px; height: 22px; background: #0066FF; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
        .dl-title { font-size: 16px; font-weight: 500; color: #001240; margin: 0 0 4px; }
        .dl-sub { font-size: 13px; color: #7090CC; margin: 0 0 1.5rem; }
        .dl-file { background: #F5F8FF; border: 0.5px solid #D0DEFF; border-radius: 8px; padding: 12px 16px; margin-bottom: 1.25rem; }
        .dl-file-name { font-size: 14px; font-weight: 500; color: #001240; margin: 0 0 2px; }
        .dl-file-size { font-size: 12px; color: #7090CC; margin: 0; }
        .dl-btn { background: #0066FF; color: #fff; border: none; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; width: 100%; cursor: pointer; text-align: center; text-decoration: none; display: block; }
        .dl-btn:hover { background: #0044CC; color: #fff; }
        .dl-success { display: none; align-items: center; gap: 8px; background: #E9F9F0; border: 0.5px solid #B8E8CC; border-radius: 8px; padding: 12px 14px; margin-top: 1rem; }
        .dl-success.show { display: flex; }
        .dl-success-icon { flex-shrink: 0; width: 20px; height: 20px; background: #1A9F5C; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .dl-success-text { font-size: 13px; font-weight: 500; color: #0F7A45; }
        .dl-note { background: #E6F0FF; border: 0.5px solid #B0CCFF; color: #0044CC; border-radius: 8px; padding: 10px 14px; font-size: 12px; margin-bottom: 1.25rem; }
    </style>
</head>
<body>
<div class="dl-wrap">
    <div class="dl-card">
        <div class="dl-logo">
            <div class="dl-logo-mark">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <line x1="1" y1="6" x2="11" y2="6" stroke="white" stroke-width="1.8"/>
                    <line x1="6" y1="1" x2="6" y2="11" stroke="white" stroke-width="1.8"/>
                </svg>
            </div>
            AXON
        </div>
        <p class="dl-brand-sub">株式会社プライムネット</p>
        <p class="dl-title">認証完了</p>
        <p class="dl-sub">以下のファイルをダウンロードできます</p>

        <div class="dl-file">
            <p class="dl-file-name">{{ $url->sharedFile->original_name }}</p>
            <p class="dl-file-size">{{ number_format($url->sharedFile->file_size / 1024, 1) }} KB</p>
            @if($url->download_limit)
            <p class="dl-file-size">ダウンロード回数: <span id="dl-count">{{ $url->download_count }}</span> / {{ $url->download_limit }}回</p>
            @endif
            <p class="dl-file-size">削除予定日: {{ $deletionDate->format('Y年m月d日') }}</p>
        </div>

        @if($url->notify_on_download)
        <div class="dl-note">ダウンロードすると、担当者に通知が送信されます。</div>
        @endif

        <a href="{{ route('download.file', $token) }}" id="dl-btn" class="dl-btn">ダウンロード</a>

        <div id="dl-success" class="dl-success">
            <div class="dl-success-icon">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <span class="dl-success-text">ダウンロードが完了しました</span>
        </div>
    </div>
</div>
<script>
document.getElementById('dl-btn').addEventListener('click', function() {
    var btn = this;
    var success = document.getElementById('dl-success');
    var countEl = document.getElementById('dl-count');
    setTimeout(function() {
        success.classList.add('show');
        btn.textContent = '再ダウンロード';
        if (countEl) {
            countEl.textContent = parseInt(countEl.textContent, 10) + 1;
        }
    }, 400);
});
</script>
</body>
</html>
