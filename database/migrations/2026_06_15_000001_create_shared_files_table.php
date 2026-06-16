<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedFilesTable extends Migration
{
    public function up()
    {
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('original_name');
            $table->string('stored_path');
            $table->unsignedBigInteger('file_size');
            $table->string('mime_type');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shared_files');
    }
}
