<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthCodesTable extends Migration
{
    public function up()
    {
        Schema::create('auth_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('download_url_id')->constrained()->onDelete('cascade');
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedTinyInteger('failed_count')->default(0);
            $table->timestamp('lock_until')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auth_codes');
    }
}
