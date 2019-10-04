<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchRecruitersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_recruiters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recruiter_id');
            $table->foreign('recruiter_id')->references('id')->on('users');
            $table->integer('candidate_id');
            $table->integer('company_id');
            $table->integer('fair_id');
            $table->integer('percentage');
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
        Schema::dropIfExists('match_recruiters');
    }
}
