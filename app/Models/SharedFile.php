<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SharedFile extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'stored_path',
        'file_size',
        'mime_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function downloadUrls()
    {
        return $this->hasMany(DownloadUrl::class);
    }
}
