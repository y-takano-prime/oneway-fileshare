<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCategoryToDownloadUrlsTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE download_urls ADD COLUMN category ENUM('business','recruitment','other') NOT NULL DEFAULT 'business' AFTER user_id");
    }

    public function down()
    {
        DB::statement("ALTER TABLE download_urls DROP COLUMN category");
    }
}
