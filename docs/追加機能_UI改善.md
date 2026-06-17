# 追加機能指示書：UI改善（実装コスト低い順）

## 注意事項

- `CLAUDE.md` の禁止ファイルは一切触れないこと
- `php artisan migrate:fresh` は実行しないこと
- 新規ファイル作成・削除は1件ずつ確認を取ること
- 同じファイルを3回以上修正したら作業を止めて報告すること
- 各項目完了ごとに「項目X完了」と報告してから次へ進むこと

---

## 項目1：ダッシュボードの直近URL一覧に相手先名を追加

**ファイル：** `resources/views/dashboard.blade.php`

テーブルの「相手先」列を `recipient_email` から `recipient_name`（なければ`recipient_email`）に変更する：

```blade
{{-- 変更前 --}}
<td>{{ $url->recipient_email }}</td>

{{-- 変更後 --}}
<td>{{ $url->recipient_name ?: $url->recipient_email }}</td>
```

ヘッダーも「相手先メール」→「相手先」に変更する。

---

## 項目2：URL一覧に相手先名を追加・有効期限切れを視覚的に区別

**ファイル：** `resources/views/urls/index.blade.php`

### 2-1. 相手先列を名前表示に変更

```blade
{{-- 変更前 --}}
<td>{{ $url->recipient_email }}</td>

{{-- 変更後 --}}
<td>
    {{ $url->recipient_name ?: $url->recipient_email }}
    <div class="text-muted small">{{ $url->recipient_email }}</div>
</td>
```

### 2-2. 有効期限切れ行をグレーアウト・バッジ表示

`@forelse` の `<tr>` を以下に変更する：

```blade
<tr class="{{ $url->expires_at->isPast() ? 'text-muted' : '' }}">
    <td><a href="{{ route('urls.show', $url) }}">{{ $url->sharedFile->original_name ?? '-' }}</a></td>
    <td>
        {{ $url->recipient_name ?: $url->recipient_email }}
        <div class="text-muted small">{{ $url->recipient_email }}</div>
    </td>
    <td>
        {{ $url->expires_at->format('Y-m-d H:i') }}
        @if ($url->expires_at->isPast())
            <span class="badge bg-secondary ms-1">期限切れ</span>
        @endif
    </td>
    <td>{{ $url->download_count }}{{ $url->download_limit ? ' / '.$url->download_limit : '' }}</td>
    <td class="text-end">
        <form method="POST" action="{{ route('urls.destroy', $url) }}" onsubmit="return confirm('無効化しますか？')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">無効化</button>
        </form>
    </td>
</tr>
```

---

## 項目3：ファイル一覧に発行済みURL件数を表示

### 3-1. `app/Http/Controllers/FileController.php`

`index()` メソッドで `downloadUrls` をカウント付きでeagerロードする：

```php
public function index()
{
    $query = SharedFile::withCount('downloadUrls');

    if (Auth::user()->role !== 'admin') {
        $query->where('user_id', Auth::id());
    }

    $files = $query->latest()->get();

    return view('files.index', ['files' => $files]);
}
```

### 3-2. `app/Models/SharedFile.php`

`downloadUrls` リレーションが未定義の場合は追加する：

```php
public function downloadUrls()
{
    return $this->hasMany(DownloadUrl::class, 'shared_file_id');
}
```

### 3-3. `resources/views/files/index.blade.php`

テーブルヘッダーに「URL数」列を追加し、各行に件数を表示する：

```blade
{{-- ヘッダーに追加 --}}
<th>URL数</th>

{{-- 各行に追加（ファイル名の次） --}}
<td>{{ $file->download_urls_count }}</td>
```

---

## 項目4：URLの有効期限・ダウンロード上限を編集できるようにする

### 4-1. `routes/web.php`

`urls` リソースに `edit` と `update` を追加する：

```php
Route::resource('urls', DownloadUrlController::class)->only(['index', 'create', 'store', 'show', 'destroy', 'edit', 'update']);
```

### 4-2. `app/Http/Controllers/DownloadUrlController.php`

`edit()` と `update()` メソッドを追加する：

```php
public function edit(DownloadUrl $url)
{
    return view('urls.edit', ['url' => $url]);
}

public function update(Request $request, DownloadUrl $url)
{
    $validated = $request->validate([
        'expires_at' => ['required', 'date', 'after:now'],
        'download_limit' => ['nullable', 'integer', 'min:1', 'max:9999'],
    ]);

    $url->update([
        'expires_at' => $validated['expires_at'],
        'download_limit' => $validated['download_limit'] ?? null,
    ]);

    return redirect()->route('urls.show', $url)->with('success', '更新しました');
}
```

### 4-3. `resources/views/urls/edit.blade.php`（新規作成）

```blade
@extends('layouts.app')

@section('content')
    <h2 class="mb-3">URL編集</h2>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('urls.update', $url) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">有効期限</label>
                    <input type="datetime-local" name="expires_at"
                        value="{{ old('expires_at', $url->expires_at->format('Y-m-d\TH:i')) }}"
                        class="form-control @error('expires_at') is-invalid @enderror">
                    @error('expires_at')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">ダウンロード上限（空欄＝無制限）</label>
                    <input type="number" name="download_limit"
                        value="{{ old('download_limit', $url->download_limit) }}"
                        class="form-control @error('download_limit') is-invalid @enderror"
                        min="1" max="9999">
                    @error('download_limit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">更新</button>
                    <a href="{{ route('urls.show', $url) }}" class="btn btn-outline-secondary">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
@endsection
```

### 4-4. `resources/views/urls/show.blade.php`

URL詳細カードに「編集」ボタンを追加する：

```blade
{{-- card-body の末尾、既存のdlの後に追加 --}}
<div class="mt-3">
    <a href="{{ route('urls.edit', $url) }}" class="btn btn-sm btn-outline-secondary">有効期限・上限を編集</a>
</div>
```

---

## 項目5：URL一覧・ファイル一覧の検索

### 5-1. `resources/views/urls/index.blade.php`

テーブルの上に検索フォームを追加する：

```blade
<form method="GET" action="{{ route('urls.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="相手先名・メール・ファイル名で検索">
        <button type="submit" class="btn btn-outline-secondary">検索</button>
        @if(request('q'))
            <a href="{{ route('urls.index') }}" class="btn btn-outline-danger">クリア</a>
        @endif
    </div>
</form>
```

### 5-2. `app/Http/Controllers/DownloadUrlController.php`

`index()` メソッドに検索処理を追加する：

```php
public function index(Request $request)
{
    $query = DownloadUrl::query()->with('sharedFile');

    if (Auth::user()->role !== 'admin') {
        $query->where('user_id', Auth::id());
    }

    if ($q = $request->input('q')) {
        $query->where(function ($q2) use ($q) {
            $q2->where('recipient_name', 'like', "%{$q}%")
               ->orWhere('recipient_email', 'like', "%{$q}%")
               ->orWhereHas('sharedFile', function ($q3) use ($q) {
                   $q3->where('original_name', 'like', "%{$q}%");
               });
        });
    }

    $urls = $query->latest()->get();

    return view('urls.index', ['urls' => $urls]);
}
```

### 5-3. `resources/views/files/index.blade.php`

ファイル一覧にも同様の検索フォームを追加する：

```blade
<form method="GET" action="{{ route('files.index') }}" class="mb-3">
    <div class="input-group" style="max-width: 400px;">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="ファイル名で検索">
        <button type="submit" class="btn btn-outline-secondary">検索</button>
        @if(request('q'))
            <a href="{{ route('files.index') }}" class="btn btn-outline-danger">クリア</a>
        @endif
    </div>
</form>
```

### 5-4. `app/Http/Controllers/FileController.php`

`index()` メソッドに検索処理を追加する：

```php
public function index(Request $request)
{
    $query = SharedFile::withCount('downloadUrls');

    if (Auth::user()->role !== 'admin') {
        $query->where('user_id', Auth::id());
    }

    if ($q = $request->input('q')) {
        $query->where('original_name', 'like', "%{$q}%");
    }

    $files = $query->latest()->get();

    return view('files.index', ['files' => $files]);
}
```
