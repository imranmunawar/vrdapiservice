<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    
        Schema::create('company_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies');
            $table->integer('fair_id')->unsigned()->nullable();
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->integer('recruiter_id')->unsigned()->nullable();
            $table->foreign('recruiter_id')->references('id')->on('users');
            $table->string('title');
            $table->longText('description');
            $table->string('job_type')->nullable();
            $table->string('language')->nullable();
            $table->string('location')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->unique();
            $table->string('url')->nullable();
            $table->string('salary')->nullable();
            $table->integer('match')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('company_jobs');
    }
}
