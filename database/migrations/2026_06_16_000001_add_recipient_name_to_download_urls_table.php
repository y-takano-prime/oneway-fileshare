<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientNameToDownloadUrlsTable extends Migration
{
    public function up()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->string('recipient_name')->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->dropColumn('recipient_name');
        });
    }
}
