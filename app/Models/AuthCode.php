<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'download_url_id',
        'code',
        'expires_at',
        'used_at',
        'failed_count',
        'lock_until',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'lock_until' => 'datetime',
    ];

    public function downloadUrl()
    {
        return $this->belongsTo(DownloadUrl::class);
    }
}
