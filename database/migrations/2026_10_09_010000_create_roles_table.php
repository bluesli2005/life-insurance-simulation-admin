<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('can_write_applications')->default(false);
            $table->boolean('can_delete_applications')->default(false);
            $table->boolean('can_manage_users')->default(false);
        });

        DB::table('roles')->insert([
            ['code' => 'viewer', 'name' => '閲覧者', 'can_write_applications' => false, 'can_delete_applications' => false, 'can_manage_users' => false],
            ['code' => 'editor', 'name' => '編集者', 'can_write_applications' => true, 'can_delete_applications' => false, 'can_manage_users' => false],
            ['code' => 'super_admin', 'name' => '最高管理者', 'can_write_applications' => true, 'can_delete_applications' => true, 'can_manage_users' => true],
        ]);

        $roleIds = DB::table('roles')->pluck('id', 'code');
        Schema::table('users', function (Blueprint $table) use ($roleIds) {
            $table->unsignedBigInteger('role_id')->default($roleIds['viewer']);
            $table->foreign('role_id')->references('id')->on('roles');
        });

        foreach ($roleIds as $code => $id) {
            DB::table('users')->where('role', $code)->update(['role_id' => $id]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('viewer');
        });

        foreach (DB::table('roles')->pluck('id', 'code') as $code => $id) {
            DB::table('users')->where('role_id', $id)->update(['role' => $code]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('roles');
    }
}
