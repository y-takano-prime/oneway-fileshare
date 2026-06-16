ファイルがダウンロードされました

以下のファイルがダウンロードされました。

ファイル名: {{ $downloadUrl->sharedFile->original_name }}
ダウンロード日時: {{ now()->format('Y-m-d H:i:s') }}
IPアドレス: {{ $ipAddress }}
相手先メールアドレス: {{ $downloadUrl->recipient_email }}
