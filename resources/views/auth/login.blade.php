<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>ログイン - oneway-fileshare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
</head>
<body class="d-flex flex-column">
<div class="page page-center">
    <div class="container container-tight py-4">
        <div class="text-center mb-4">
            <h1>oneway-fileshare</h1>
        </div>
        <form class="card card-md" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="card-body">
                <h2 class="card-title text-center mb-4">ログイン</h2>
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                <div class="mb-3">
                    <label class="form-label">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" autofocus>
                </div>
                <div class="mb-2">
                    <label class="form-label">パスワード</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="form-footer">
                    <button type="submit" class="btn btn-primary w-100">ログイン</button>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
