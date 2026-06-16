<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessLogsTable extends Migration
{
    public function up()
    {
        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_url_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->enum('action', ['access', 'passcode_ok', 'passcode_fail', 'email_ok', 'email_fail', 'otp_ok', 'otp_fail', 'download']);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('access_logs');
    }
}
