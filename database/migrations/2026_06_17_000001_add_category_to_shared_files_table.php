<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToSharedFilesTable extends Migration
{
    public function up()
    {
        Schema::table('shared_files', function (Blueprint $table) {
            $table->enum('category', ['business', 'recruitment'])->nullable()->after('mime_type');
        });
    }

    public function down()
    {
        Schema::table('shared_files', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
