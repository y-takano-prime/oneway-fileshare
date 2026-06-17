<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>AXON — エラー</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { background: #F5F8FF; font-family: system-ui, -apple-system, sans-serif; margin: 0; }
        .dl-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
        .dl-card { background: #fff; border: 0.5px solid #D0DEFF; border-radius: 12px; padding: 2rem 2.5rem; width: 100%; max-width: 420px; }
        .dl-logo { display: flex; align-items: center; gap: 7px; font-size: 15px; font-weight: 600; color: #001240; letter-spacing: -0.02em; margin-bottom: 1.75rem; justify-content: center; }
        .dl-logo-mark { width: 22px; height: 22px; background: #0066FF; border-radius: 4px; display: flex; align-items: center; justify-content: center; }
        .dl-error-box { background: #FFF4E0; border: 0.5px solid #F0D090; border-radius: 8px; padding: 1rem 1.25rem; text-align: center; }
        .dl-error-title { font-size: 14px; font-weight: 500; color: #7A4F00; margin: 0 0 4px; }
        .dl-error-msg { font-size: 13px; color: #9B6200; margin: 0; }
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
        @php
            $messages = [
                'expired' => ['このURLの有効期限は切れています。', '担当者に新しいURLを発行してもらってください。'],
                'limit'   => ['ダウンロード可能回数の上限に達しています。', '担当者にお問い合わせください。'],
                'invalid' => ['このURLは無効です。', 'URLが正しいかご確認ください。'],
            ];
            [$title, $sub] = $messages[$reason] ?? $messages['invalid'];
        @endphp
        <div class="dl-error-box">
            <p class="dl-error-title">{{ $title }}</p>
            <p class="dl-error-msg">{{ $sub }}</p>
        </div>
    </div>
</div>
</body>
</html>
