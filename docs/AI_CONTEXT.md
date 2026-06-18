# AI_CONTEXT — AXON (oneway-fileshare) プロジェクト文脈

> ⚠️ **作業開始前に必ず `CLAUDE.md` を読み、そこに記載されたルール（読み取り専用ファイル・禁止コマンド・DB絶対ルール・ループ防止ルール・ファイル作成/削除の確認ルール）に従うこと。**
> 本ファイルはCLAUDE.mdを補足するプロジェクト文脈情報であり、CLAUDE.mdのルールを上書きしない。CLAUDE.mdとAI_CONTEXT.mdの内容が矛盾する場合はCLAUDE.mdを優先する。

---

## 0. クイックスタート

| 項目 | 内容 |
|------|------|
| ローカルURL | `http://oneway-fileshare.local:8080` |
| DB名 | `oneway_fileshare`（接続情報は `.env` 参照。本ファイルに秘匿情報は書かない） |
| テストログイン（管理者） | `admin@example.com` / `password`（`AdminUserSeeder` による初期データ） |
| Git | originと連携済みの通常のgitリポジトリ。`main`ブランチで運用 |
| 開発ログ記録 | `log_start.bat`（開始時）/ `log_end.bat`（終了時）を実行 → `docs/開発ログ.md` に追記 |

---

## 1. プロジェクト概要

- **システム名（裏側のコード名）:** oneway-fileshare
- **UI上のブランド名:** AXON（ホワイト×エレクトリックブルー `#0066FF` のデザインに刷新済み）
- **目的:** メール添付によるファイル授受の課題（迷惑メール判定・誤送信・容量制限・履歴管理困難）を解消する、当社→相手先の片方向ファイル授受Webシステム
- **授受の方向:** 当社（担当者）→ 相手先のみ。相手先はダウンロードのみでアップロード機能なし
- **開発期間:** 約3ヶ月のプロトタイプ開発（2026年6月15日開始）
- **要件定義書:** `docs/要件定義書.md`（読み取り専用）
- **設計仕様書:** `docs/設計仕様書.md`（読み取り専用、初期設計時点の内容。後続の追加機能で一部更新されていない箇所がある点に注意）

---

## 2. 想定ユーザー

| 種別 | 権限・特徴 |
|------|-----------|
| 管理者（admin） | 全データにアクセス可能。ユーザー管理・アクセスログ・システム設定を操作可能 |
| 担当者（staff） | 自分が発行したURL・アップロードしたファイルの管理のみ。社員コード・会社ID（P/M/T/H）・部署（depts）を持つ |
| 相手先（外部・認証不要） | 発行されたURLからメールアドレス確認＋OTP認証を経てダウンロードのみ可能 |

---

## 3. 必須機能一覧

### 外部向け（相手先）
- URLアクセス → メールアドレス入力 → OTP（6桁・10分有効）入力 → ダウンロード（**パスコード入力ステップは廃止済み**、`download_urls.passcode`カラムはDBに残存するが未使用）
- 認証失敗5回でロック
- アクセスログ記録（access/email_ok/email_fail/otp_ok/otp_fail/download等）

### 担当者向け
- ファイルアップロード → URL発行を1フローに統合（3ステップ：①ファイル選択 ②送付先設定 ③メール文章確認・コピー）
- ステップ2で入力：属性（取引先/採用/その他、**必須**）、企業名（任意・採用選択時は自動非表示）、役職部署（任意・同上）、相手先名、メールアドレス、有効期限、DL上限、備考（任意）、ダウンロード時通知の有無
- 送付メール文章の自動生成・コピー機能（`DownloadUrlController::buildMailText()`）

### 管理者向け
- ユーザー管理（社員コード・会社ID・部署・権限・状態の追加/編集/削除）
- アクセスログ一覧
- システム設定（パスコード必須化・自動削除猶予日数・削除前通知の有無）

### 共通
- 自動削除：有効期限切れ後、猶予期間（デフォルト7日）経過で物理削除。`fileshare:cleanup` Artisanコマンド（`app/Console/Commands/CleanupExpiredFiles.php`）。削除前通知・削除ログ記録あり
- ダウンロード時の担当者への通知メール（任意設定）

---

## 4. 技術スタック・実行環境の制約

| 項目 | 内容 |
|------|------|
| フレームワーク | Laravel 8 |
| PHP | **7.4.10**（XAMPP同梱） |
| DB | MySQL（XAMPP同梱） |
| フロントエンド | Blade + 独自AXON CSS（後述。元はBootstrap/Tabler、リブランディングで全面置換済み） |
| メール送信 | SMTP（Kagoya、固定アカウント運用。資格情報は `.env` 参照） |

### PHP 7.4特有の制約（実際にハマった事例あり）
- **nullsafe演算子 `?->` は使用不可**（PHP 8.0以降のみ）。`$obj?->prop` のような記法を書くと500エラーになる。`$obj && $obj->prop` の形で書くこと
- `match()` 式も使用不可。`switch` または `if/elseif` を使う
- アロー関数 `fn() => ...` は使用可（PHP 7.4で導入済み）

### Blade / ルーティングの実態（指示書記載と異なる箇所あり）
- レイアウトのスクリプト差し込みは **`@yield('scripts')`** を使用（`resources/views/layouts/app.blade.php` 参照）。`docs/追加機能_UI全面刷新.md` 等の古い指示書には `@stack('scripts')` の記載があるが、現状は `@yield` 方式に統一されているため新規ビューも `@section('scripts') ... @endsection` で揃えること
- 管理者系のルート名は実際には **`admin.users.index`** / `admin.logs.index` / `admin.settings.index`（`routes/web.php` で `prefix('admin')->name('admin.')` グループ配下）。古い指示書中の `users.index` 等の記載は誤り
- クリップボードコピーは `navigator.clipboard` が使えない場合（HTTP/非secureコンテキスト）、`document.execCommand('copy')` にフォールバックする実装にすること

---

## 5. 現在のディレクトリ構成（実態）

```
oneway-fileshare/
├── app/
│   ├── Console/Commands/
│   │   └── CleanupExpiredFiles.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/{UserController, AccessLogController, SettingController}.php
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── FileController.php
│   │   │   ├── DownloadUrlController.php   ← buildMailText()を保有
│   │   │   └── DownloadController.php      ← 相手先向け（認証不要）
│   │   └── Middleware/
│   │       └── AdminMiddleware.php（role!==adminは403）
│   ├── Mail/
│   │   ├── OtpMail.php（HTMLメール、view()送信）
│   │   ├── DownloadNotificationMail.php
│   │   └── DeleteNotificationMail.php
│   └── Models/
│       ├── User.php / SharedFile.php / DownloadUrl.php
│       ├── AccessLog.php / AuthCode.php / DeletedFilesLog.php
│       └── （depts テーブルはModelなし。DB::table('depts')で直接参照）
├── database/
│   ├── migrations/（18ファイル。2026_06_15〜2026_06_17、詳細は6章参照）
│   └── seeders/{DatabaseSeeder, AdminUserSeeder, DummyDataSeeder}.php
├── docs/
│   ├── 要件定義書.md / 設計仕様書.md / 実装指示書.md（読み取り専用）
│   ├── 追加機能_*.md（パスコード廃止/リブランディング/UI全面刷新/UI改善/メール文章コピー/SMTP動的認証）
│   ├── 開発ログ.md（読み取り専用・追記のみ）
│   └── AI_CONTEXT.md（本ファイル）
├── resources/views/
│   ├── layouts/app.blade.php（AXON共通レイアウト・CSS本体）
│   ├── auth/login.blade.php
│   ├── dashboard.blade.php
│   ├── files/index.blade.php
│   ├── urls/{index, create, create_step2, complete, show, edit}.blade.php
│   ├── download/{passcode, otp, complete, error}.blade.php（相手先向け、独立デザイン）
│   ├── admin/{users/{index,create,edit}, logs/index, settings/index}.blade.php
│   └── emails/{otp, download_notification, delete_notification}.blade.php
├── routes/web.php
├── log_start.bat / log_end.bat
└── CLAUDE.md
```

---

## 6. データベース設計（現状）

初期設計（要件定義書・設計仕様書）から以下が追加・変更されている。**マイグレーションファイルは読み取り専用**のため変更時は新規マイグレーションを追加すること。

| テーブル | 主な項目（初期設計＋追加分） |
|---------|---------|
| users | id, name, **employee_code**, email, password, role, is_active, **company_id**（ENUM P/M/T/H, nullable）, **dept_id**（FK→depts, nullable） |
| depts（新規） | id, name, color, dept_level, start_number, sort_number, deleted_at, timestamps（8部署を初期投入済み。idは1-6,90,91） |
| shared_files | id, user_id, original_name, stored_path, file_size, mime_type, **category**（business/recruitment/**other**） |
| download_urls | id, shared_file_id, user_id, **category**（ENUM business/recruitment/other, **NOT NULL DEFAULT 'business'**, sharedFile.categoryとは別管理）, token, passcode（未使用）, recipient_name, **company_name**, **recipient_title**, recipient_email, expires_at, download_limit, download_count, notify_on_download, **memo**, deleted_at |
| access_logs | id, download_url_id, ip_address, action, created_at |
| auth_codes | id, download_url_id, code, expires_at, used_at, failed_count, lock_until |
| deleted_files_log | id, original_name, stored_path, deleted_by, deleted_at |

---

## 7. AXON デザインシステム（CSSクラス一覧）

定義場所：`resources/views/layouts/app.blade.php`（管理画面共通）／ `download/*.blade.php` 内に独立スタイル（`dl-*` クラス、ログイン不要画面用）

**カラー：** プライマリ `#0066FF` ／ 背景 `#F5F8FF` ／ カード枠 `#D0DEFF` ／ テキスト主 `#001240` ／ テキスト副 `#7090CC`

| 用途 | クラス名 |
|------|---------|
| カード | `axon-card` |
| テーブル | `axon-table`（`th`/`td`は自動スタイル） |
| 入力欄・ラベル | `axon-input` / `axon-label` |
| ボタン | `btn-axon`（塗り） / `btn-axon-outline`（枠線） / `btn-axon-ghost`（控えめ） / `btn-axon-danger`（削除系） |
| アラート | `axon-alert-success` / `axon-alert-error` |
| ステップバー | `axon-steps` / `axon-step`（`.active`で強調） |
| 状態バッジ | `badge-dl`（DL済み） / `badge-wait`（未DL） / `badge-expired`（期限切れ） |
| 属性バッジ | `badge-business`（取引先） / `badge-recruitment`（採用） / `badge-other`（その他） |
| メトリクス | `axon-stat` / `axon-stat-num` / `axon-stat-label` |

**⚠️ 重要な注意（実際に発生した事故）：** 既存のbladeファイルを `Write` ツールで全文置換すると、AXON CSSが古いBootstrap/Tablerスタイルに戻ってしまった事例がある。既存ファイルを修正する際は `Edit` ツール（差分編集）を優先し、`Write`での全置換は避けること。

---

## 8. 実装上の規約・パターン

- **属性（category）は2系統ある**：`shared_files.category`（ファイル自体の属性）と `download_urls.category`（URL発行時に必須選択する属性）は別カラムで意味も別。表示時はどちらを参照すべきか文脈で判断する（URL一覧・ダッシュボードは`download_urls.category`を使用）
- **検索は2系統に分離**：一般検索 `q`（相手先名/ファイル名/メールアドレス）と担当者名検索 `staff_q`（管理者専用、`DownloadUrlController::index()`）。同一人物が相手先と担当者の両方に存在する場合の誤マッチを防ぐため統合しない
- **スペース無視の氏名検索**：`REPLACE(REPLACE(name, ' ', ''), '　', '') LIKE ?` で全角/半角スペース挿入有無に関わらずヒットさせる
- **depts はModelを作らない**：`DB::table('depts')->whereNull('deleted_at')->orderBy('sort_number')->get()` で直接参照する運用（新規ファイル作成を避ける方針）
- **メール本文は一箇所に集約**：`DownloadUrlController::buildMailText(DownloadUrl $url)` private メソッドが `show()` と `complete()` の両方から呼ばれる。宛名は「会社名／役職部署／（空行）／氏名 様」の順
- **OTPメールはHTML**：`OtpMail` は `->view('emails.otp')` を使用（`->text()`ではない）。認証コードを `<strong>` で強調表示するため

---

## 9. 進行状況チェックリスト

### 完了済み
- [x] 基本機能（Step1-9）実装：モデル・ミドルウェア・Mail・コントローラー・ルーティング・ビュー・自動削除コマンド・Seeder
- [x] パスコード入力ステップ廃止 → メール+OTPの2段階認証に変更
- [x] SMTP送信エラー解決（Kagoyaアカウント名形式の特定）
- [x] UI全面刷新（サイドバー→ヘッダーナビ、3ステップ新規作成フロー）
- [x] AXONリブランディング（ホワイト×エレクトリックブルー）
- [x] ファイル属性（category）追加、「その他」区分追加
- [x] depts テーブル新設、users への company_id/dept_id/employee_code 追加
- [x] 管理者ユーザー管理画面の項目拡張（社員コード・会社・部署列）
- [x] URL検索機能を相手先検索／担当者名検索に分離
- [x] ダッシュボード・URL管理一覧のレイアウト統一
- [x] URL発行フォームへの企業名・役職部署・属性（必須）・備考欄追加
- [x] 送付メールテンプレートの宛名フォーマット修正
- [x] OTPメールのHTML化（コード強調・空行調整）
- [x] 2026-06-17時点までの作業をgitコミット済み、`docs/開発ログ.md`に記録済み

### 未着手・将来検討（要件定義書「将来拡張」に記載のスコープ外項目）
- [ ] ウイルスチェック機能（本番運用前に対応予定）
- [ ] クラウドストレージ連携（OneDrive / Google Drive）
- [ ] 双方向ファイル授受（Laravelバージョンアップ後に検討）
- [ ] 電子契約連携・OCR履歴書解析・AI自動分類・ChatGPT連携

---

## 10. 新しいチャットでの文脈復元用テンプレート

新しいClaude Codeセッションでこのプロジェクトの作業を依頼する際は、以下の手順を踏むこと。

1. まず `CLAUDE.md` を読み、記載されたルール（読み取り専用ファイル・禁止コマンド・DB絶対ルール等）を必ず守る
2. 次に本ファイル `docs/AI_CONTEXT.md` を読み、プロジェクトの現状・技術的な制約・実装規約を把握する
3. 直近の変更履歴を知りたい場合は `docs/開発ログ.md` の末尾（最新日付のセクション）と `git log --oneline -10` を確認する
4. 作業前に `git status` で現在の状態を確認する
5. 不明点があれば作業前にユーザーに確認する（特に新規ファイル作成・削除、DB変更を伴う場合）

ユーザーへの依頼文の例：
> 「`CLAUDE.md` と `docs/AI_CONTEXT.md` を読んで、このoneway-fileshare（AXON）プロジェクトの文脈を理解してください。その上で〇〇を実装してください。」
