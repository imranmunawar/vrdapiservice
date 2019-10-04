<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('job_id');
            $table->foreign('job_id')->references('id')->on('company_jobs');
            $table->integer('candidate_id');
            $table->integer('company_id');
            $table->integer('fair_id');
            $table->integer('percentage');
            $table->integer('recruiter_id');
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
        Schema::dropIfExists('match_jobs');
    }
}
