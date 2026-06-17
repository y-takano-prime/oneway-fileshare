<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyAndDeptToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('company_id', ['P', 'M', 'T', 'H'])->nullable()->after('role');
            $table->unsignedBigInteger('dept_id')->nullable()->after('company_id');
            $table->foreign('dept_id')->references('id')->on('depts')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['dept_id']);
            $table->dropColumn(['company_id', 'dept_id']);
        });
    }
}
