<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimulationApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('simulation_applications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('application_number', 50)->unique();
            $table->string('applicant_name', 100);
            $table->string('insured_name', 100);
            $table->date('insured_birth_date');
            $table->string('beneficiary_name', 100)->nullable();
            $table->decimal('coverage_amount', 15, 2);
            $table->decimal('premium_amount', 15, 2);
            $table->char('currency', 3)->default('JPY');
            $table->string('status', 20);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'effective_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('simulation_applications');
    }
}
