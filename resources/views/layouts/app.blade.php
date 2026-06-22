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
        .axon-nav-links { display: flex; align-items: center; gap: 0.75rem; }
        .axon-nav-toggle {
            display: none;
            background: transparent;
            border: 1px solid #D0DEFF;
            border-radius: 6px;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }
        .axon-id-dropdown { position: relative; }
        .axon-id-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 4px 8px 4px 4px;
            border-radius: 20px;
        }
        .axon-id-trigger:hover { background: #EEF4FF; }
        .axon-id-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #0066FF;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .axon-id-name {
            font-size: 12px;
            font-weight: 600;
            color: #001240;
            white-space: nowrap;
        }
        .axon-id-menu {
            display: none;
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: #fff;
            border: 0.5px solid #D0DEFF;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 18, 64, 0.12);
            padding: 2px 18px;
            min-width: 240px;
            z-index: 200;
            text-align: center;
        }
        .axon-id-menu.open { display: block; }
        .axon-id-menu-row {
            padding: 10px 0;
            border-bottom: 0.5px solid #D0DEFF;
        }
        .axon-id-menu-name {
            font-size: 13px;
            font-weight: 600;
            color: #001240;
            letter-spacing: 0.08em;
            padding: 12px 0 10px;
        }
        .axon-id-menu-meta {
            font-size: 13px;
            color: #001240;
        }
        .axon-id-menu-email {
            font-size: 13px;
            color: #001240;
            word-break: break-all;
        }
        .axon-id-menu-logout {
            padding: 10px 0 12px;
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
        .axon-stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
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
            font-size: 12px;
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
            font-size: 14px;
            color: #001240;
            border-bottom: 1px solid #D4DFF5;
            vertical-align: middle;
        }
        .axon-table tr:hover td { background: #EEF4FF; }
        .axon-table tr:last-child td { border-bottom: none; }
        .axon-table tr.row-expired td { background: #E4E4E6; }
        .axon-table tr.row-invalidated td { background: #FFF0F0; }

        /* バッジ */
        .badge-dl          { background: #E6F0FF; color: #0044CC; font-size: 12px; padding: 3px 9px; border-radius: 20px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-wait        { background: #FFF4E0; color: #9B6200; font-size: 12px; padding: 3px 9px; border-radius: 20px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-expired     { background: #E3E3E3; color: #5C5C5C; font-size: 12px; padding: 3px 9px; border-radius: 20px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-invalidated { background: #FFF0F0; color: #CC0000; font-size: 12px; padding: 3px 9px; border-radius: 20px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-business    { background: #EEF4FF; color: #0044CC; font-size: 12px; padding: 3px 9px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-recruitment { background: #E8F7F0; color: #006E42; font-size: 12px; padding: 3px 9px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }
        .badge-other       { background: #F5F0FF; color: #5500AA; font-size: 12px; padding: 3px 9px; border-radius: 4px; font-weight: 500; white-space: nowrap; letter-spacing: .01em; }

        /* ストレージバー */
        .axon-bar { height: 4px; background: #D0DEFF; border-radius: 2px; overflow: hidden; margin-top: 8px; }
        .axon-bar-fill { height: 100%; background: #0066FF; border-radius: 2px; }
        .axon-bar-fill.warn { background: #D4880A; }

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

        /* チェックボックス式フィルターピル */
        .axon-checkbox-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #D0DEFF;
            background: #fff;
            color: #7090CC;
            cursor: pointer;
            user-select: none;
        }
        .axon-checkbox-pill input[type="checkbox"] {
            accent-color: #0066FF;
            width: 13px;
            height: 13px;
            margin: 0;
        }
        .axon-checkbox-pill:has(input:checked) {
            background: #0066FF;
            color: #fff;
            border-color: #0066FF;
            font-weight: 500;
        }
        .axon-checkbox-pill-count {
            font-size: 11px;
            color: #B0C0E0;
        }
        .axon-checkbox-pill:has(input:checked) .axon-checkbox-pill-count {
            color: rgba(255, 255, 255, 0.8);
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
        .axon-alert-warning {
            background: #FFF4E0;
            border: 0.5px solid #FFD699;
            color: #9B6200;
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

        /* テーブルのスマホ用横スクロールラッパー */
        .axon-table-wrap { width: 100%; }

        /* スマホ対応（ナビ折りたたみ・テーブル横スクロール・各要素の折り返し） */
        @media (max-width: 768px) {
            .axon-navbar { height: auto; }
            .axon-navbar-inner { flex-wrap: wrap; padding: 0.75rem 1rem; }
            .axon-nav-toggle { display: flex; }
            .axon-nav-links {
                display: none;
                width: 100%;
                flex-basis: 100%;
                order: 3;
                flex-direction: column;
                align-items: stretch;
                gap: 6px;
                margin-top: 0.75rem;
            }
            .axon-nav-links.open { display: flex; }
            .axon-nav-link { text-align: center; }
            .axon-content { padding: 1rem; }
            .axon-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .axon-table { width: auto; min-width: 640px; }
            .axon-steps { flex-wrap: wrap; gap: 0.5rem 0; }
            .axon-step { margin-right: 1rem; }
            .axon-stat-grid { grid-template-columns: repeat(2, 1fr); }
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

    <button type="button" id="axon-nav-toggle" class="axon-nav-toggle" aria-label="メニュー">
        <svg width="18" height="14" viewBox="0 0 18 14" fill="none">
            <line x1="0" y1="1" x2="18" y2="1" stroke="#001240" stroke-width="1.8"/>
            <line x1="0" y1="7" x2="18" y2="7" stroke="#001240" stroke-width="1.8"/>
            <line x1="0" y1="13" x2="18" y2="13" stroke="#001240" stroke-width="1.8"/>
        </svg>
    </button>

    <div id="axon-nav-links" class="axon-nav-links">
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
    </div>

    <div class="axon-nav-right">
        @php
            $idMetaParts = array_filter([
                Auth::user()->company_id,
                Auth::user()->employee_code,
                Auth::user()->deptName(),
            ]);
        @endphp
        <div class="axon-id-dropdown">
            <button type="button" id="axon-id-trigger" class="axon-id-trigger">
                <div class="axon-id-avatar">{{ mb_substr(Auth::user()->name, 0, 1) }}</div>
                <span class="axon-id-name">{{ Auth::user()->name }}</span>
                <svg width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1L5 5L9 1" stroke="#7090CC" stroke-width="1.5"/></svg>
            </button>
            <div id="axon-id-menu" class="axon-id-menu">
                @if($idMetaParts)
                <div class="axon-id-menu-row axon-id-menu-meta">{{ implode(' / ', $idMetaParts) }}</div>
                @endif
                <div class="axon-id-menu-row axon-id-menu-name">{{ Auth::user()->name }}</div>
                <div class="axon-id-menu-row axon-id-menu-email">{{ Auth::user()->email }}</div>
                <form method="POST" action="{{ route('logout') }}" class="axon-id-menu-logout">
                    @csrf
                    <button type="submit" class="axon-logout" style="width:100%">ログアウト</button>
                </form>
            </div>
        </div>
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
<script>
document.addEventListener('click', function (e) {
    var trigger = document.getElementById('axon-id-trigger');
    var menu = document.getElementById('axon-id-menu');
    if (!trigger || !menu) return;
    if (trigger.contains(e.target)) {
        menu.classList.toggle('open');
    } else if (!menu.contains(e.target)) {
        menu.classList.remove('open');
    }
});

document.addEventListener('click', function (e) {
    var navToggle = document.getElementById('axon-nav-toggle');
    var navLinks = document.getElementById('axon-nav-links');
    if (!navToggle || !navLinks) return;
    if (navToggle.contains(e.target)) {
        navLinks.classList.toggle('open');
    } else if (!navLinks.contains(e.target)) {
        navLinks.classList.remove('open');
    }
});
</script>
@yield('scripts')
</body>
</html>
