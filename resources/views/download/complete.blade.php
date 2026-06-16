<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ダウンロード - oneway-fileshare</title>
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
    <p class="text-muted mb-1">{{ $url->sharedFile->original_name }}</p>
    <p class="text-muted mb-4">{{ number_format($url->sharedFile->file_size / 1024, 1) }} KB</p>
    <a href="{{ route('download.file', $token) }}" class="btn btn-primary w-100">ダウンロード</a>
</div>
</body>
</html>
