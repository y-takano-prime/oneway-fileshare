<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeletedFilesLogTable extends Migration
{
    public function up()
    {
        Schema::create('deleted_files_log', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->foreignId('deleted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('deleted_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deleted_files_log');
    }
}
