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
            $table->integer('presenter_id')->unsigned();
            $table->foreign('presenter_id')->references('id')->on('frontdesks');
            $table->integer('organiser_id')->unsigned();
            $table->foreign('organiser_id')->references('id')->on('users');
            $table->integer('receptionist_id')->unsigned();
            $table->foreign('receptionist_id')->references('id')->on('frontdesks');
            $table->string('name', 60);
            $table->string('short_name', 30);
            $table->string('email', 30);
            $table->string('phone', 15);
            $table->string('fair_image', 30)->nullable();
            $table->string('fair_video', 30)->nullable();
            $table->string('timezone', 50)->nullable();
            $table->dateTime('register_time')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->tinyInteger('fair_type')->default(0);
            $table->string('website', 60);
            $table->string('facebook', 60);
            $table->string('youtube', 60);
            $table->string('twitter', 60);
            $table->string('linkedin', 60);
            $table->string('instagram', 60);
            $table->tinyInteger('status')->default(0);

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
