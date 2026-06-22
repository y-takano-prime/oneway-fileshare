<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>AXON — 認証コード入力</title>
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
        .dl-label { font-size: 11px; color: #7090CC; letter-spacing: .04em; text-transform: uppercase; display: block; margin-bottom: 5px; font-weight: 500; }
        .dl-input { border: 1px solid #D0DEFF; border-radius: 6px; padding: 10px 14px; font-size: 22px; color: #001240; background: #fff; width: 100%; margin-bottom: 12px; text-align: center; letter-spacing: .3em; font-weight: 500; }
        .dl-input:focus { outline: none; border-color: #0066FF; box-shadow: 0 0 0 3px rgba(0,102,255,.1); }
        .dl-btn { background: #0066FF; color: #fff; border: none; border-radius: 6px; padding: 11px; font-size: 14px; font-weight: 500; width: 100%; cursor: pointer; }
        .dl-btn:hover { background: #0044CC; }
        .dl-error { background: #FFF0F0; color: #CC0000; border-radius: 6px; padding: 8px 12px; font-size: 13px; margin-bottom: 12px; }
        .dl-warn { background: #FFF4E0; color: #9B6200; border-radius: 6px; padding: 8px 12px; font-size: 13px; margin-bottom: 12px; }
        .dl-lock { background: #FFF0F0; border: 0.5px solid #FFB0B0; color: #CC0000; border-radius: 8px; padding: 1rem; font-size: 13px; text-align: center; }
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

        @if(!empty($locked))
            <div class="dl-lock">
                認証に5回失敗したためロックされています。<br>
                <strong>{{ $lockUntil->format('H:i') }}</strong> 以降に再度お試しください。
            </div>
        @else
            <p class="dl-title">認証コードを入力</p>
            <p class="dl-sub">メールで送信された6桁のコードを入力してください</p>

            @if(!empty($error))
                <div class="dl-error">{{ $error }}</div>
            @endif
            @if(isset($remaining))
                <div class="dl-warn">残り {{ $remaining }} 回試行できます</div>
            @endif

            <form method="POST" action="{{ route('download.verify-otp', $token) }}">
                @csrf
                <label class="dl-label">認証コード（6桁）</label>
                <input type="text" name="code" maxlength="6" inputmode="numeric" class="dl-input" autofocus required>
                <button type="submit" class="dl-btn">認証する</button>
            </form>
        @endif
    </div>
</div>
</body>
</html>
