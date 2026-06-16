<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>認証コード入力 - oneway-fileshare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f5f7; }
        .panel { max-width: 420px; width: 100%; padding: 3rem 2rem; background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(0,0,0,.08); text-align: center; }
        .panel h1 { font-size: 1.4rem; margin-bottom: 2rem; }
    </style>
</head>
<body>
<div class="panel">
    <h1>oneway-fileshare</h1>
    @if (!empty($locked))
        <div class="alert alert-danger">
            認証に5回失敗したため、しばらくロックされています。<br>
            {{ $lockUntil->format('H:i') }} 以降に再度お試しください。
        </div>
    @else
        @if (!empty($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif
        @if (isset($remaining))
            <div class="text-muted mb-3">残り{{ $remaining }}回試行できます</div>
        @endif
        <p class="text-muted mb-4">メールで送信された認証コード（6桁）を入力してください</p>
        <form method="POST" action="{{ route('download.verify-otp', $token) }}">
            @csrf
            <input type="text" name="code" maxlength="6" inputmode="numeric" class="form-control mb-3 text-center" autofocus required>
            <button type="submit" class="btn btn-primary w-100">認証する</button>
        </form>
    @endif
</div>
</body>
</html>
