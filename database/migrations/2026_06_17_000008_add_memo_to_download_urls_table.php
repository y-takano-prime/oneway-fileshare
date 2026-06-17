<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMemoToDownloadUrlsTable extends Migration
{
    public function up()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->text('memo')->nullable()->after('notify_on_download');
        });
    }

    public function down()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->dropColumn('memo');
        });
    }
}
