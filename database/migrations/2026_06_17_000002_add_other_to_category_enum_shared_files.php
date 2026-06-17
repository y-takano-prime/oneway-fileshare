<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOtherToCategoryEnumSharedFiles extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE shared_files MODIFY COLUMN category ENUM('business', 'recruitment', 'other') NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE shared_files MODIFY COLUMN category ENUM('business', 'recruitment') NULL");
    }
}
