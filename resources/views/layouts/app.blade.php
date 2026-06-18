<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>AXON</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
        /* AXONカラーシステム */
        body { background: #F5F8FF; }

        /* ヘッダーナビ */
        .axon-navbar {
            background: #ffffff;
            border-bottom: 2px solid #0066FF;
            height: 78px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .axon-navbar-inner {
            max-width: 1200px;
            margin: 0 auto;
            height: 100%;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .axon-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #001240;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.02em;
            flex-shrink: 0;
        }
        .axon-logo-mark {
            width: 36px;
            height: 36px;
            background: #0066FF;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .axon-nav-link {
            color: #001240;
            font-size: 13px;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid #D0DEFF;
            background: #fff;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }
        .axon-nav-link:hover { background: #E6F0FF; color: #0044CC; border-color: #B0CCFF; }
        .axon-nav-link.active { background: #0066FF; color: #fff; border-color: #0066FF; font-weight: 500; }
        .axon-nav-right { margin-left: auto; display: flex; align-items: center; gap: 12px; }
        .axon-id-card {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .axon-id-card-item {
            font-size: 12px;
            color: #001240;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }
        .axon-id-card-name {
            font-size: 12px;
            color: #001240;
            font-weight: 600;
            white-space: nowrap;
        }
        .axon-logout {
            font-size: 12px;
            color: #001240;
            text-decoration: none;
            padding: 4px 10px;
            border: 1px solid #D0DEFF;
            border-radius: 6px;
            background: transparent;
            cursor: pointer;
        }
        .axon-logout:hover { background: #E6F0FF; color: #0044CC; }

        /* メインコンテンツ */
        .axon-content { padding: 1.5rem; max-width: 1200px; margin: 0 auto; }

        /* カード */
        .axon-card {
            background: #fff;
            border: 0.5px solid #D0DEFF;
            border-radius: 8px;
            padding: 1rem 1.25rem;
        }

        /* メトリクスカード */
        .axon-stat {
            background: #fff;
            border: 0.5px solid #D0DEFF;
            border-radius: 8px;
            padding: 12px 14px;
        }
        .axon-stat-num {
            font-size: 26px;
            font-weight: 500;
            color: #001240;
            line-height: 1;
        }
        .axon-stat-label {
            font-size: 10px;
            color: #7090CC;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* テーブル */
        .axon-table { width: 100%; border-collapse: collapse; }
        .axon-table th {
            font-size: 11px;
            color: #4A6595;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 8px 12px;
            border-bottom: 2px solid #B8CCF0;
            text-align: left;
            font-weight: 600;
        }
        .axon-table td {
            padding: 10px 12px;
            font-size: 13px;
            color: #001240;
            border-bottom: 1px solid #D4DFF5;
            vertical-align: middle;
        }
        .axon-table tr:hover td { background: #EEF4FF; }
        .axon-table tr:last-child td { border-bottom: none; }

        /* バッジ */
        .badge-dl          { background: #E6F0FF; color: #0044CC; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 500; white-space: nowrap; }
        .badge-wait        { background: #FFF4E0; color: #9B6200; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 500; white-space: nowrap; }
        .badge-expired     { background: #F2F2F2; color: #999; font-size: 11px; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }
        .badge-invalidated { background: #FFF0F0; color: #CC0000; font-size: 11px; padding: 3px 8px; border-radius: 20px; font-weight: 500; white-space: nowrap; }
        .badge-business    { background: #EEF4FF; color: #0044CC; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-recruitment { background: #E8F7F0; color: #006E42; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-other       { background: #F5F0FF; color: #5500AA; font-size: 10px; padding: 2px 7px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }

        /* ストレージバー */
        .axon-bar { height: 4px; background: #D0DEFF; border-radius: 2px; overflow: hidden; margin-top: 8px; }
        .axon-bar-fill { height: 100%; background: #0066FF; border-radius: 2px; }

        /* ボタン */
        .btn-axon {
            background: #0066FF;
            color: #fff;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-axon:hover { background: #0044CC; color: #fff; }
        .btn-axon-outline {
            background: transparent;
            color: #0066FF;
            border: 1px solid #0066FF;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-axon-outline:hover { background: #E6F0FF; color: #0044CC; }
        .btn-axon-ghost {
            background: transparent;
            color: #7090CC;
            border: 1px solid #D0DEFF;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-axon-ghost:hover { background: #F5F8FF; color: #001240; }
        .btn-axon-danger {
            background: transparent;
            color: #CC0000;
            border: 1px solid #FFB0B0;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-axon-danger:hover { background: #FFF0F0; }

        /* フォーム */
        .axon-input {
            border: 1px solid #D0DEFF;
            border-radius: 6px;
            padding: 7px 12px;
            font-size: 13px;
            color: #001240;
            background: #fff;
            width: 100%;
            box-sizing: border-box;
        }
        .axon-input:focus {
            outline: none;
            border-color: #0066FF;
            box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.1);
        }
        .axon-label {
            font-size: 11px;
            color: #7090CC;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        /* アラート */
        .axon-alert-success {
            background: #E6F0FF;
            border: 0.5px solid #B0CCFF;
            color: #0044CC;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 1rem;
        }
        .axon-alert-error {
            background: #FFF0F0;
            border: 0.5px solid #FFB0B0;
            color: #CC0000;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 1rem;
        }

        /* ステップバー */
        .axon-steps { display: flex; gap: 0; margin-bottom: 1.5rem; }
        .axon-step {
            font-size: 12px;
            padding: 6px 14px 6px 0;
            color: #7090CC;
            border-bottom: 2px solid #D0DEFF;
            margin-right: 1.5rem;
        }
        .axon-step.active {
            color: #0066FF;
            border-bottom-color: #0066FF;
            font-weight: 500;
        }
    </style>
</head>
<body>

{{-- ヘッダーナビ --}}
@auth
<nav class="axon-navbar">
<div class="axon-navbar-inner">
    <a href="{{ route('dashboard') }}" class="axon-logo">
        <div class="axon-logo-mark">
            <svg width="20" height="20" viewBox="0 0 13 13" fill="none">
                <line x1="1" y1="6.5" x2="12" y2="6.5" stroke="white" stroke-width="1.8"/>
                <line x1="6.5" y1="1" x2="6.5" y2="12" stroke="white" stroke-width="1.8"/>
            </svg>
        </div>
        AXON
    </a>

    <a href="{{ route('dashboard') }}" class="axon-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        ダッシュボード
    </a>

    @if(Auth::user()->role !== 'admin')
    <a href="{{ route('urls.create') }}" class="axon-nav-link {{ request()->routeIs('urls.create') || request()->routeIs('urls.create_step2') ? 'active' : '' }}">
        新規作成
    </a>
    @endif

    <a href="{{ route('urls.index') }}" class="axon-nav-link {{ request()->routeIs('urls.*') && !request()->routeIs('urls.create') && !request()->routeIs('urls.create_step2') && !request()->routeIs('urls.complete') ? 'active' : '' }}">
        URL管理
    </a>

    @if(Auth::user()->role === 'admin')
    <a href="{{ route('admin.users.index') }}" class="axon-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        ユーザー管理
    </a>
    <a href="{{ route('admin.logs.index') }}" class="axon-nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
        ログ
    </a>
    <a href="{{ route('admin.storage.index') }}" class="axon-nav-link {{ request()->routeIs('admin.storage.*') ? 'active' : '' }}">
        ストレージ
    </a>
    <a href="{{ route('admin.settings.index') }}" class="axon-nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
        設定
    </a>
    @endif

    <div class="axon-nav-right">
        <div class="axon-id-card">
            @if(Auth::user()->company_id)
            <span class="axon-id-card-item">{{ Auth::user()->company_id }}</span>
            @endif
            @if(Auth::user()->employee_code)
            <span class="axon-id-card-item">{{ Auth::user()->employee_code }}</span>
            @endif
            <span class="axon-id-card-name">{{ Auth::user()->name }}</span>
            @if(Auth::user()->deptName())
            <span class="axon-id-card-item">{{ Auth::user()->deptName() }}</span>
            @endif
            <span class="axon-id-card-item">{{ Auth::user()->email }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="axon-logout">ログアウト</button>
        </form>
    </div>
</div>
</nav>
@endauth

{{-- メインコンテンツ --}}
<main class="axon-content">
    @if(session('success'))
        <div class="axon-alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="axon-alert-error">{{ session('error') }}</div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
@yield('scripts')
</body>
</html>
