<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyWebinarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_webinars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id');
            $table->integer('fair_id');
            $table->integer('recruiter_id');
            $table->string('title');
            $table->string('type');
            $table->integer('match');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->longText('description');
            $table->string('link');
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
        Schema::dropIfExists('company_webinars');
    }
}
