<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyAndTitleToDownloadUrlsTable extends Migration
{
    public function up()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->string('company_name', 255)->nullable()->after('recipient_name');
            $table->string('recipient_title', 255)->nullable()->after('company_name');
        });
    }

    public function down()
    {
        Schema::table('download_urls', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'recipient_title']);
        });
    }
}
