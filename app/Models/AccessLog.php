<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'download_url_id',
        'ip_address',
        'action',
    ];

    protected $attributes = [
        'created_at' => null,
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function downloadUrl()
    {
        return $this->belongsTo(DownloadUrl::class);
    }

    public function getActionLabelAttribute()
    {
        $labels = [
            'access'     => 'URLアクセス',
            'email_ok'   => 'メールアドレス確認 成功',
            'email_fail' => 'メールアドレス確認 失敗',
            'otp_ok'     => '認証コード確認 成功',
            'otp_fail'   => '認証コード確認 失敗',
            'download'   => 'ダウンロード',
        ];

        return $labels[$this->action] ?? $this->action;
    }
}
