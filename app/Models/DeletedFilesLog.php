<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedFilesLog extends Model
{
    protected $table = 'deleted_files_log';

    public $timestamps = false;

    protected $fillable = [
        'original_name',
        'stored_path',
        'deleted_by',
    ];
}
