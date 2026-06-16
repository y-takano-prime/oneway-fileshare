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
}
