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
        .dl-logo { display: flex; align-items: center; gap: 7px; font-size: 15px; font-weight: 600; color: #001240; letter-spacing: -0.02em; margin-bottom: 1.75rem; justify-content: center; }
        .dl-logo-mark { width: 22px; height: 22px; background: #0066FF; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
        .dl-title { font-size: 16px; font-weight: 500; color: #001240; margin: 0 0 4px; }
        .dl-sub { font-size: 13px; color: #7090CC; margin: 0 0 1.5rem; }
        .dl-file { background: #F5F8FF; border: 0.5px solid #D0DEFF; border-radius: 8px; padding: 12px 16px; margin-bottom: 1.25rem; }
        .dl-file-name { font-size: 14px; font-weight: 500; color: #001240; margin: 0 0 2px; }
        .dl-file-size { font-size: 12px; color: #7090CC; margin: 0; }
        .dl-btn { background: #0066FF; color: #fff; border: none; border-radius: 6px; padding: 12px; font-size: 14px; font-weight: 500; width: 100%; cursor: pointer; text-align: center; text-decoration: none; display: block; }
        .dl-btn:hover { background: #0044CC; color: #fff; }
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
        <p class="dl-title">認証完了</p>
        <p class="dl-sub">以下のファイルをダウンロードできます</p>

        <div class="dl-file">
            <p class="dl-file-name">{{ $url->sharedFile->original_name }}</p>
            <p class="dl-file-size">{{ number_format($url->sharedFile->file_size / 1024, 1) }} KB</p>
        </div>

        <a href="{{ route('download.file', $token) }}" class="dl-btn">ダウンロード</a>
    </div>
</div>
</body>
</html>
