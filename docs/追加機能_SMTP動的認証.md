# 追加機能指示書：SMTP動的認証（ログインユーザーのメールアドレスで送信）

## 概要

OTPメール送信時に、URLを発行した担当者のメールアドレス・パスワードを使ってSMTP認証する。
担当者のシステムログインパスワード = Kagoyaメールパスワード として運用する。

---

## 実装済みの変更（確認のみ）

以下はすでに変更済み。内容を確認し、問題があれば修正すること。

### 1. `app/Http/Controllers/Auth/LoginController.php`

ログイン成功後にパスワードをセッションへ保存する処理を追加済み：

```php
$request->session()->regenerate();

// OTPメール送信用にパスワードをセッションへ一時保存
session(['smtp_password' => $request->input('password')]);

return redirect()->intended(route('dashboard'));
```

### 2. `app/Http/Controllers/DownloadController.php`

`verifyEmail()` メソッド内のOTP送信前に、セッションのパスワードでSMTP設定を上書きする処理を追加済み：

```php
$senderEmail = optional($url->user)->email;
$smtpPassword = session('smtp_password');

if ($senderEmail && $smtpPassword) {
    config([
        'mail.mailers.smtp.username' => $senderEmail,
        'mail.mailers.smtp.password' => $smtpPassword,
    ]);
}
```

### 3. `.env`

SMTP認証情報を空にし、サーバー情報のみ設定済み：

```env
MAIL_MAILER=smtp
MAIL_HOST=mss122.kagoya.net
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 未対応・要対応

### 4. `app/Mail/OtpMail.php` の送信元アドレス設定確認

`build()` メソッドで `$senderEmail` を `from()` にセットしている。
`.env` の `MAIL_FROM_ADDRESS=null` のままでは `from()` を指定しないと送信元が空になる可能性があるため、
`$senderEmail` が存在する場合は必ず `from()` をセットすること（実装済みのはずだが確認すること）。

### 5. `app/Http/Controllers/DownloadController.php` の `download()` メソッド

ダウンロード通知メール（`notify_on_download`）も同様にセッションのパスワードを使って送信すること：

```php
if ($url->notify_on_download) {
    try {
        $smtpPassword = session('smtp_password');
        if ($smtpPassword && $url->user) {
            config([
                'mail.mailers.smtp.username' => $url->user->email,
                'mail.mailers.smtp.password' => $smtpPassword,
            ]);
        }
        Mail::to($url->user->email)->send(new DownloadNotificationMail($url, request()->ip()));
    } catch (\Throwable $e) {
        report($e);
    }
}
```

---

## 動作確認手順

1. `php artisan config:clear` を実行
2. `http://oneway-fileshare.local:8080/login` にログイン
   - メール：`y.takano@prime-gr.jp`
   - パスワード：Kagoyaメールパスワードと同じ値
3. ファイルをアップロードしてURLを発行
4. 発行したURLへアクセスしてOTP認証まで進める
5. 相手先メールアドレスにOTPメールが届くことを確認

---

## 管理者のURL発行制限

管理者（`role: admin`）はKagoyaメールアカウントを持たないため、URL発行・OTP送信ができない。
以下の制限を追加すること。

### 6. `app/Http/Controllers/DownloadUrlController.php`

`create()` と `store()` メソッドの先頭に管理者チェックを追加する：

```php
public function create(Request $request)
{
    if (Auth::user()->role === 'admin') {
        abort(403, 'URL発行は担当者のみ操作できます');
    }
    // 以降既存の処理
}

public function store(Request $request)
{
    if (Auth::user()->role === 'admin') {
        abort(403, 'URL発行は担当者のみ操作できます');
    }
    // 以降既存の処理
}
```

### 7. URL一覧画面のURL発行ボタン非表示

`resources/views/urls/index.blade.php` の「URL発行」ボタンを管理者には表示しない：

```blade
@if (Auth::user()->role !== 'admin')
    <a href="{{ route('urls.create') }}" class="btn btn-primary">URL発行</a>
@endif
```

---

## 動作確認手順

1. `php artisan config:clear` を実行
2. `http://oneway-fileshare.local:8080/login` にログイン
   - メール：`y.takano@prime-gr.jp`
   - パスワード：Kagoyaメールパスワードと同じ値
3. ファイルをアップロードしてURLを発行
4. 発行したURLへアクセスしてOTP認証まで進める
5. 相手先メールアドレスにOTPメールが届くことを確認
6. 管理者でログインしてURL発行ボタンが表示されないことを確認

---

## 注意事項

- `CLAUDE.md` の禁止ファイルは一切触れないこと
- `php artisan migrate:fresh` は実行しないこと
- 同じファイルを3回以上修正しようとした場合は作業を止めて報告すること
