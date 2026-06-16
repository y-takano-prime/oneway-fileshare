<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDownloadUrlsTable extends Migration
{
    public function up()
    {
        Schema::create('download_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shared_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('passcode')->nullable();
            $table->string('recipient_email');
            $table->timestamp('expires_at');
            $table->unsignedInteger('download_limit')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('notify_on_download')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('download_urls');
    }
}
