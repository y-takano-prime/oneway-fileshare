<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDeptsTable extends Migration
{
    public function up()
    {
        Schema::create('depts', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->string('name', 100);
            $table->string('color', 20)->default('#f0f0f0');
            $table->tinyInteger('dept_level')->default(0);
            $table->integer('start_number')->default(0);
            $table->integer('sort_number')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('depts')->insert([
            ['id' => 1,  'name' => '経営企画部',    'color' => '#6B8866', 'dept_level' => 0, 'start_number' => 9601, 'sort_number' => 3, 'deleted_at' => null, 'created_at' => '2019-07-26 11:38:28', 'updated_at' => '2019-12-06 15:15:47'],
            ['id' => 2,  'name' => 'システム開発部', 'color' => '#E65A50', 'dept_level' => 0, 'start_number' => 9501, 'sort_number' => 4, 'deleted_at' => null, 'created_at' => '2019-07-26 14:03:41', 'updated_at' => '2023-10-06 08:24:22'],
            ['id' => 3,  'name' => 'Web営業企画部', 'color' => '#C99E10', 'dept_level' => 0, 'start_number' => 9401, 'sort_number' => 5, 'deleted_at' => null, 'created_at' => '2019-07-26 13:57:52', 'updated_at' => '2023-10-06 08:24:22'],
            ['id' => 4,  'name' => 'デザイン企画部', 'color' => '#1995AD', 'dept_level' => 9, 'start_number' => 0,    'sort_number' => 9, 'deleted_at' => null, 'created_at' => '2019-07-26 14:58:19', 'updated_at' => '2019-07-26 14:58:19'],
            ['id' => 5,  'name' => 'Web制作開発部', 'color' => '#5D535E', 'dept_level' => 0, 'start_number' => 9301, 'sort_number' => 6, 'deleted_at' => null, 'created_at' => '2019-07-26 15:17:13', 'updated_at' => '2023-10-06 08:24:22'],
            ['id' => 6,  'name' => 'プロダクト企画部','color' => '#fd7e00', 'dept_level' => 0, 'start_number' => 9701, 'sort_number' => 7, 'deleted_at' => null, 'created_at' => '2023-10-06 08:24:22', 'updated_at' => '2023-10-06 08:24:22'],
            ['id' => 90, 'name' => '共通',           'color' => '#f0f0f0', 'dept_level' => 1, 'start_number' => 9101, 'sort_number' => 1, 'deleted_at' => null, 'created_at' => '2023-02-03 15:49:07', 'updated_at' => '2023-02-03 15:49:07'],
            ['id' => 91, 'name' => '戦略会議',       'color' => '#f0f0f0', 'dept_level' => 1, 'start_number' => 9201, 'sort_number' => 2, 'deleted_at' => null, 'created_at' => '2023-02-03 15:49:07', 'updated_at' => '2023-02-03 15:49:07'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('depts');
    }
}
