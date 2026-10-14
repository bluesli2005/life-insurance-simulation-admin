<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationAudit extends Migration
{
    public function up()
    {
        Schema::table('simulation_applications', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('simulation_application_audits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('actor_user_id');
            $table->string('action', 20);
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->timestamp('created_at');

            $table->foreign('application_id')->references('id')->on('simulation_applications');
            $table->foreign('actor_user_id')->references('id')->on('users');
            $table->index(['application_id', 'id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('simulation_application_audits');
        Schema::table('simulation_applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
