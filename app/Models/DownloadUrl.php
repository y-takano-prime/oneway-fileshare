<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadUrl extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'shared_file_id',
        'user_id',
        'category',
        'token',
        'passcode',
        'recipient_name',
        'company_name',
        'recipient_title',
        'recipient_email',
        'expires_at',
        'download_limit',
        'download_count',
        'notify_on_download',
        'memo',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'notify_on_download' => 'boolean',
    ];

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('download_limit')
                    ->orWhereColumn('download_count', '<', 'download_limit');
            });
    }

    public function sharedFile()
    {
        return $this->belongsTo(SharedFile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessLogs()
    {
        return $this->hasMany(AccessLog::class);
    }

    public function authCode()
    {
        return $this->hasOne(AuthCode::class)->latestOfMany();
    }
}
