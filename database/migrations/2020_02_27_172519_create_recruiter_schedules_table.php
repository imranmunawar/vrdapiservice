<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruiterSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiter_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recruiter_id');
            $table->integer('fair_id');
            $table->integer('company_id');
            $table->tinyInteger('candidate_id')->default(0);
            $table->string('start_time');
            $table->string('end_time');
            $table->string('days');
            $table->text('days_arr');
            $table->string('status')->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruiter_schedules');
    }
}
