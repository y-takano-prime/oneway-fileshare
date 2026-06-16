<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>oneway-fileshare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
</head>
<body>
<div class="page">
    <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <h1 class="navbar-brand navbar-brand-autodark">
                <a href="{{ route('dashboard') }}">oneway-fileshare</a>
            </h1>
            <div class="collapse navbar-collapse" id="sidebar-menu">
                <ul class="navbar-nav pt-lg-3">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">ダッシュボード</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('files.index') }}">ファイル管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('urls.index') }}">URL管理</a>
                    </li>
                    @if (auth()->user() && auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">ユーザー管理</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.logs.index') }}">アクセスログ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.settings.index') }}">設定</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </aside>
    <header class="navbar navbar-expand-md d-print-none">
        <div class="container-xl">
            <div class="navbar-nav flex-row order-md-last ms-auto align-items-center">
                <span class="nav-link">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="ms-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">ログアウト</button>
                </form>
            </div>
        </div>
    </header>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
@yield('scripts')
</body>
</html>
