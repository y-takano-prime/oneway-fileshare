# oneway-fileshare プロジェクト概要

## ⚠️ AIへの重要な指示

### 絶対に編集・削除してはいけないファイル

以下のファイルはユーザーの明示的な指示がない限り、**読み取り専用**として扱うこと：

- `CLAUDE.md`
- `docs/要件定義書.md`
- `docs/開発ログ.md`
- `docs/設計仕様書.md`
- `docs/実装指示書.md`
- `log_start.bat`
- `log_end.bat`
- `.env`
- `composer.json`
- `composer.lock`
- `artisan`
- `database/migrations/` 配下のすべてのファイル

### 禁止コマンド（ユーザーの明示的な許可なく実行禁止）

- `php artisan migrate:fresh`
- `php artisan migrate:reset`
- `php artisan db:wipe`
- `composer update`
- `composer install`
- `rm`・`del` などのファイル削除コマンド

### ループ・暴走防止ルール

- 同じファイルを3回以上連続して修正しようとしている場合は**作業を止めてユーザーに状況を報告**すること
- エラーが解消しない場合も同様に**3回試みたら止めて報告**すること
- 複数ファイルの一括削除は**いかなる理由があっても禁止**
- 作業開始前に必ず `git status` で現在の状態を確認すること

### ファイル作成・削除のルール

- 新規ファイルの作成はユーザーに確認してから実行すること
- ファイルの削除は1件ずつユーザーに確認してから実行すること

## システム概要

当社→相手先への片方向ファイル授受Webシステム。メール添付に代わり、ブラウザ上で安全にファイルを授受する。

- **システム名:** oneway-fileshare
- **開発期間:** 約3ヶ月（プロトタイプ）
- **要件定義書:** `docs/要件定義書.md`
- **開発ログ:** `docs/開発ログ.md`

## 利用フロー

1. 担当者が管理画面からファイルをアップロード
2. 担当者がダウンロード用URLを発行（有効期限・パスコード・相手先メールアドレスを設定）
3. 担当者が相手先へURLをメールで通知
4. 相手先がURLにアクセス→パスコード入力→メールアドレス入力→ワンタイムコード認証→ダウンロード
5. 担当者が管理画面でダウンロード履歴を確認

**相手先の操作はダウンロードのみ（アップロード機能なし）**

## 技術構成

| 項目 | 内容 |
|------|------|
| フレームワーク | Laravel 8 |
| PHP | 7.4.10（XAMPP） |
| データベース | MySQL（XAMPP同梱） |
| フロントエンド | Blade + Bootstrap |
| ファイル保存 | ローカルストレージ |
| 開発環境 | XAMPP on Windows |
| ローカルURL | http://oneway-fileshare.local:8080 |

## ディレクトリ構成

```
C:\xampp\projects\oneway-fileshare\
├── app/
│   ├── Http/Controllers/
│   └── Models/
├── database/migrations/
├── docs/
│   ├── 要件定義書.md
│   └── 開発ログ.md
├── public/                  ← Apacheのドキュメントルート
├── resources/views/
├── routes/web.php
├── log_start.bat            ← 作業開始時に実行
├── log_end.bat              ← 作業終了時に実行
└── CLAUDE.md
```

## データベース設計

| テーブル | 主な項目 |
|---------|---------|
| users | id, name, email, password, role, is_active, created_at |
| shared_files | id, user_id, original_name, stored_path, file_size, mime_type, created_at |
| download_urls | id, shared_file_id, user_id, token, passcode, recipient_email, expires_at, download_limit, download_count, notify_on_download, deleted_at |
| access_logs | id, download_url_id, ip_address, action, created_at |
| auth_codes | id, download_url_id, code, expires_at, used_at, failed_count, lock_until |
| deleted_files_log | id, original_name, stored_path, deleted_by, deleted_at |

## 2段階認証フロー（相手先）

1. URLにアクセス
2. パスコードを入力
3. メールアドレスを入力（登録済みアドレスと一致確認）
4. ワンタイムコード（6桁・10分有効）をメール送信
5. コード入力→認証完了→ダウンロード画面
6. 5回失敗でロック

## ユーザー種別

| 種別 | 権限 |
|------|------|
| 管理者 | 全機能・全データにアクセス可能 |
| 担当者 | 自分が発行したURLの管理のみ |
| 相手先（外部） | 発行されたURLからダウンロードのみ |

## 将来拡張（スコープ外）

- 双方向ファイル授受（Laravelバージョンアップ後に検討）
- クラウドストレージ連携
- ウイルスチェック（本番運用前に対応）

## 環境構築メモ

- `composer.json` に `"platform": {"php": "7.4.10"}` を設定済み
- Apacheリスンポート：8080
- 既存社内システムは `C:\xampp\htdocs\` にあり、このプロジェクトとは分離
- VirtualHost設定済み：`C:\xampp\apache\conf\extra\httpd-vhosts.conf`

## 開発ログの記録方法

```
作業開始時: log_start.bat をダブルクリック（またはcmd /c log_start.bat）
作業終了時: log_end.bat をダブルクリック（またはcmd /c log_end.bat）
```
