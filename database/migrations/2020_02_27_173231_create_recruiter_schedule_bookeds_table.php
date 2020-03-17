<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruiterScheduleBookedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiter_schedule_bookeds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('u_id');
            $table->integer('fair_id');
            $table->integer('recruiter_id');
            $table->integer('candidate_id');
            $table->string('start_time');
            $table->string('end_time');
            $table->string('date');
            $table->tinyInteger('attended')->default(0);
            $table->tinyInteger('is_approved')->default(0);
            $table->string('meeting_id');
            $table->string('host_id');
            $table->text('start_url');
            $table->text('join_url');
            $table->string('password');
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
        Schema::dropIfExists('recruiter_schedule_bookeds');
    }
}
