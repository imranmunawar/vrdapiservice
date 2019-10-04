<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fairs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organiser_id')->unsigned();
            $table->foreign('organiser_id')->references('id')->on('users');
            $table->string('name', 60);
            $table->string('short_name', 30);
            $table->string('email', 30);
            $table->string('phone', 15);
            $table->string('fair_image')->nullable();
            $table->string('fair_video')->nullable();
            $table->string('fair_mobile_image')->nullable();
            $table->string('timezone')->nullable();
            $table->dateTime('register_time')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('fair_type');
            $table->string('website');
            $table->string('facebook');
            $table->string('youtube');
            $table->string('twitter');
            $table->string('linkedin');
            $table->string('instagram');
            $table->string('fair_status');
            $table->string('chat_status');
            $table->string('layout');
            $table->string('presenter');
            $table->string('stand_receptionist');
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
        Schema::dropIfExists('fairs');
    }
}
