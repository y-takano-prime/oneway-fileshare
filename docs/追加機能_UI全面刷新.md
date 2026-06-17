# 実装指示書：UI全面刷新

## 概要

以下の変更を行う。

1. レイアウトをサイドバーからヘッダーナビに変更
2. ファイル管理画面を廃止し「新規作成」に一本化（アップロード→URL発行→メールコピーを1フローで完結）
3. ダッシュボード・URL管理の一覧列を統一（相手先・メールアドレス・ファイル名・作成日・有効期限・DL数・状態）
4. ダッシュボードにストレージ使用量を追加

---

## 注意事項

- `CLAUDE.md` の禁止ファイルは一切触れないこと
- `php artisan migrate:fresh` は実行しないこと
- 新規ファイル作成・削除は1件ずつ確認を取ること
- 同じファイルを3回以上修正したら作業を止めて報告すること
- 各項目完了ごとに「項目X完了」と報告してから次へ進むこと

---

## 項目1：レイアウト変更（サイドバー→ヘッダーナビ）

**ファイル：** `resources/views/layouts/app.blade.php`

Tablerのサイドバー構成をヘッダーナビに作り直す。Bootstrap 5ベース（TablerのCDNはそのまま使用可）。

### ヘッダー構成

```
[ロゴ: oneway-fileshare]  [ダッシュボード] [新規作成] [URL管理] [管理者メニュー※]  [ユーザー名] [ログアウト]
```

※管理者メニュー（ユーザー管理・アクセスログ・設定）は管理者ログイン時のみ表示

### 実装コード

```blade
<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>oneway-fileshare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <style>
        .navbar-brand { font-size: 15px; font-weight: 500; }
        .nav-link.active { background: rgba(0,0,0,0.06); border-radius: 6px; }
    </style>
</head>
<body>
<div class="page">
    <header class="navbar navbar-expand-md navbar-light sticky-top border-bottom" style="background:#fff;">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                oneway-fileshare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav-menu">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav-menu">
                <ul class="navbar-nav me-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-medium' : '' }}" href="{{ route('dashboard') }}">ダッシュボード</a>
                    </li>
                    @if (auth()->user()->role !== 'admin')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('urls.create') ? 'active fw-medium' : '' }}" href="{{ route('urls.create') }}">新規作成</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('urls.*') && !request()->routeIs('urls.create') ? 'active fw-medium' : '' }}" href="{{ route('urls.index') }}">URL管理</a>
                    </li>
                    @if (auth()->user()->role === 'admin')
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">管理者メニュー</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">ユーザー管理</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.logs.index') }}">アクセスログ</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}">設定</a></li>
                        </ul>
                    </li>
                    @endif
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted small">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <div class="page-wrapper">
        <div class="page-body">
            <div class="container-xl py-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible mb-3">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible mb-3">{{ session('error') }}</div>
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
```

---

## 項目2：新規作成フロー（3ステップ）

ファイル管理画面（`/files`）は残しつつ、新規作成フローを作る。

### ステップ構成

```
Step 1: ファイルアップロード（またはアップロード済みから選択）
Step 2: 送付先設定（相手先名・メール・有効期限・DL上限・通知設定）
Step 3: メール文章確認・コピー → 完了
```

### 2-1. ルート追加（`routes/web.php`）

既存の `urls` ルートはそのままに、新規作成用の多段フローをセッションで管理する。
`urls.create` → Step 1（ファイル選択＋アップロード）
`urls.store` → Step 1 → Step 2へリダイレクト（セッションにfile_id保存）
`urls.confirm` → Step 2（送付先設定）
`urls.store_confirm` → Step 2 → Step 3へリダイレクト（URLを発行し、show画面でメール表示）

```php
// 既存のresourceルートに加えて追加
Route::get('urls/{url}/complete', [DownloadUrlController::class, 'complete'])->name('urls.complete');
```

### 2-2. `resources/views/urls/create.blade.php` を作り直す

**Step 1：ファイル選択＋アップロード**

- ページ上部にステップバーを表示（Step1 active）
- ドラッグ＆ドロップでアップロードエリア
- アップロード済みファイル一覧から選択できるテーブルも表示
- ファイルを選択または新規アップロードしたら「次へ」ボタンでStep 2へ
- `shared_file_id` をセッションに保存して `urls.create_step2` へリダイレクト

```blade
{{-- ステップバー --}}
<div class="d-flex mb-4 border-bottom">
    <div class="pb-2 me-4 border-bottom border-primary text-primary fw-medium" style="font-size:13px;">1. ファイル選択</div>
    <div class="pb-2 me-4 text-muted" style="font-size:13px;">2. 送付先設定</div>
    <div class="pb-2 text-muted" style="font-size:13px;">3. メール確認・送信</div>
</div>
```

**Step 2：送付先設定**（`resources/views/urls/create_step2.blade.php` 新規作成）

- ステップバー（Step 2 active）
- 選択ファイル名をカード表示（変更不可）
- 相手先名・メールアドレス・有効期限・DL上限・通知設定を入力
- 送信すると `DownloadUrl` を作成して `urls.complete` へリダイレクト

**Step 3：完了・メールコピー**（`resources/views/urls/complete.blade.php` 新規作成）

- ステップバー（Step 3 active）
- 発行したURL詳細をカード表示
- メール文章テキストエリアと「全文をコピー」ボタン
- 「URL管理へ戻る」ボタン

### 2-3. `app/Http/Controllers/DownloadUrlController.php` の修正

`store()` メソッドをStep 1→2の流れに対応させる：

```php
// Step1: ファイル選択受け取り → セッション保存 → Step2へ
public function storeStep1(Request $request)
{
    $request->validate(['shared_file_id' => ['required', 'exists:shared_files,id']]);
    session(['create_file_id' => $request->shared_file_id]);
    return redirect()->route('urls.create_step2');
}

// Step2: 送付先設定受け取り → URL発行 → complete へ
public function store(Request $request)
{
    if (Auth::user()->role === 'admin') { abort(403); }

    $validated = $request->validate([
        'recipient_name'  => ['required', 'string', 'max:255'],
        'recipient_email' => ['required', 'email', 'max:255'],
        'expires_at'      => ['required', 'date', 'after:now'],
        'download_limit'  => ['nullable', 'integer', 'min:1', 'max:9999'],
        'notify_on_download' => ['boolean'],
    ]);

    $fileId = session('create_file_id');
    if (!$fileId) return redirect()->route('urls.create');

    $url = DownloadUrl::create([
        'shared_file_id'    => $fileId,
        'user_id'           => Auth::id(),
        'token'             => Str::random(64),
        'recipient_name'    => $validated['recipient_name'],
        'recipient_email'   => $validated['recipient_email'],
        'expires_at'        => $validated['expires_at'],
        'download_limit'    => $validated['download_limit'] ?? null,
        'download_count'    => 0,
        'notify_on_download'=> $request->boolean('notify_on_download'),
    ]);

    session()->forget('create_file_id');
    return redirect()->route('urls.complete', $url);
}

// Step3: 完了・メールコピー画面
public function complete(DownloadUrl $url)
{
    $url->load('sharedFile');

    $mailText = implode("\n", [
        '件名：ファイルのご案内',
        '',
        $url->recipient_name . ' 様',
        '',
        'お世話になっております。',
        '以下のURLよりファイルをダウンロードいただけます。',
        '',
        '■ファイル名',
        $url->sharedFile->original_name,
        '',
        '■ダウンロードURL',
        route('download.passcode', $url->token),
        '',
        '■有効期限',
        $url->expires_at->format('Y年m月d日 H:i'),
        '',
        'ダウンロード後は手順に従いメール認証を完了してください。',
        '',
        'よろしくお願いいたします。',
    ]);

    return view('urls.complete', ['url' => $url, 'mailText' => $mailText]);
}
```

### 2-4. ルート追加

```php
Route::get('urls/create/step2', [DownloadUrlController::class, 'createStep2'])->name('urls.create_step2');
Route::post('urls/step1', [DownloadUrlController::class, 'storeStep1'])->name('urls.store_step1');
Route::get('urls/{url}/complete', [DownloadUrlController::class, 'complete'])->name('urls.complete');
```

**注意：** `urls/create/step2` と `urls/step1` は `urls` resource より前に定義すること。

---

## 項目3：ダッシュボード一覧の列統一＋ストレージ表示

**ファイル：** `resources/views/dashboard.blade.php`

### 3-1. 一覧の列順を変更

```
相手先 → メールアドレス → ファイル名 → 作成日 → 有効期限 → DL数 → 状態
```

```blade
<thead>
    <tr>
        <th>相手先</th>
        <th>メールアドレス</th>
        <th>ファイル名</th>
        <th>作成日</th>
        <th>有効期限</th>
        <th>DL数</th>
        <th>状態</th>
    </tr>
</thead>
<tbody>
    @forelse ($recentUrls as $url)
        <tr class="{{ $url->expires_at->isPast() ? 'text-muted' : '' }}">
            <td class="fw-medium">{{ $url->recipient_name }}</td>
            <td class="text-muted small">{{ $url->recipient_email }}</td>
            <td>{{ $url->sharedFile->original_name ?? '-' }}</td>
            <td class="text-muted small">{{ $url->created_at->format('Y-m-d') }}</td>
            <td class="small">{{ $url->expires_at->format('Y-m-d H:i') }}</td>
            <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
            <td>
                @if ($url->expires_at->isPast())
                    <span class="badge bg-secondary">期限切れ</span>
                @else
                    <span class="badge bg-success">有効</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted">データがありません</td></tr>
    @endforelse
</tbody>
```

### 3-2. ストレージ使用量カードを追加

**ファイル：** `app/Http/Controllers/DashboardController.php`

```php
use App\Models\SharedFile;
use Illuminate\Support\Facades\Storage;

// index() に追加
$storageQuery = SharedFile::query();
if (Auth::user()->role !== 'admin') {
    $storageQuery->where('user_id', Auth::id());
}
$totalSize = $storageQuery->sum('file_size');
$fileCount  = $storageQuery->count();
```

`view()` に渡す：
```php
'totalSize' => $totalSize,
'fileCount' => $fileCount,
```

**ビュー側（`dashboard.blade.php`）にストレージカードを追加：**

```blade
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-1" style="font-size:13px;">
            <span class="text-muted">ストレージ使用量</span>
            <span class="text-muted">ファイル数：{{ $fileCount }}件</span>
        </div>
        @php
            $usedMB  = round($totalSize / 1024 / 1024, 1);
            $limitMB = 1024;
            $percent = min(100, round($usedMB / $limitMB * 100));
        @endphp
        <div class="progress mb-1" style="height:8px;">
            <div class="progress-bar" style="width:{{ $percent }}%"></div>
        </div>
        <div class="d-flex justify-content-between" style="font-size:12px;color:#888;">
            <span>使用中：{{ $usedMB }} MB</span>
            <span>{{ $percent }}% / 1 GB</span>
        </div>
    </div>
</div>
```

---

## 項目4：URL管理一覧の列統一

**ファイル：** `resources/views/urls/index.blade.php`

ダッシュボードと同じ列順に変更する：

```
相手先 → メールアドレス → ファイル名 → 作成日 → 有効期限 → DL数 → 状態 → 操作
```

```blade
<thead>
    <tr>
        <th>相手先</th>
        <th>メールアドレス</th>
        <th>ファイル名</th>
        <th>作成日</th>
        <th>有効期限</th>
        <th>DL数</th>
        <th>状態</th>
        <th></th>
    </tr>
</thead>
<tbody>
    @forelse ($urls as $url)
        <tr class="{{ $url->expires_at->isPast() ? 'text-muted' : '' }}">
            <td class="fw-medium">{{ $url->recipient_name }}</td>
            <td class="text-muted small">{{ $url->recipient_email }}</td>
            <td><a href="{{ route('urls.show', $url) }}">{{ $url->sharedFile->original_name ?? '-' }}</a></td>
            <td class="text-muted small">{{ $url->created_at->format('Y-m-d') }}</td>
            <td class="small">
                {{ $url->expires_at->format('Y-m-d H:i') }}
                @if ($url->expires_at->isPast())
                    <span class="badge bg-secondary ms-1">期限切れ</span>
                @endif
            </td>
            <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
            <td>
                @if ($url->expires_at->isPast())
                    <span class="badge bg-secondary">期限切れ</span>
                @else
                    <span class="badge bg-success">有効</span>
                @endif
            </td>
            <td class="text-end">
                <a href="{{ route('urls.show', $url) }}" class="btn btn-sm btn-outline-secondary">詳細</a>
                <form method="POST" action="{{ route('urls.destroy', $url) }}" class="d-inline" onsubmit="return confirm('無効化しますか？')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">無効化</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted">URLがありません</td></tr>
    @endforelse
</tbody>
```

---

## 動作確認手順

1. `php artisan config:clear` を実行
2. ログインしてヘッダーナビが表示されることを確認
3. 「新規作成」からStep 1→2→3の流れでURL発行できることを確認
4. Step 3でメール文章コピーボタンが動作することを確認
5. ダッシュボードにストレージ使用量が表示されることを確認
6. ダッシュボード・URL管理の列順が正しいことを確認
