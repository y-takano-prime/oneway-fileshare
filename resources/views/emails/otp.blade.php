<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<style>
  body { font-family: sans-serif; font-size: 14px; color: #222; line-height: 1.8; }
  .wrap { max-width: 560px; margin: 0 auto; padding: 24px; }
  .code-block { margin: 0; padding: 14px 20px; background: #F0F5FF; border-left: 4px solid #0066FF; border-radius: 4px; }
  .code-label { font-size: 11px; color: #7090CC; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 4px; }
  .code-value { font-size: 28px; font-weight: 700; letter-spacing: .2em; color: #001240; }
  .footer { font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 12px; }
</style>
</head>
<body>
<div class="wrap">

    <p style="margin:0">認証コードのご案内</p>
    <br>
    <p style="margin:0">以下のファイルのダウンロードのため、認証コードを入力してください。</p>
    <br>
    <p style="margin:0">ファイル名: {{ $fileName }}</p>
    <br>
    <div class="code-block">
        <div class="code-label">認証コード</div>
        <div class="code-value"><strong>{{ $code }}</strong></div>
    </div>
    <br>
    <p style="margin:0">有効期限: 発行から10分間</p>
    <br>
    <div class="footer">このメールに心当たりがない場合は破棄してください。</div>

</div>
</body>
</html>
