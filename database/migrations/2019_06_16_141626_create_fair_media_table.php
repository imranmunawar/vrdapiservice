<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFairMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fair_media', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fair_id')->unsigned()->nullable();
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->string('fair_media_name');
            $table->string('fair_media_type');
            $table->integer('fair_media_description')->nullable();
            $table->string('fair_media_link')->nullable();
            $table->string('fair_media_image')->nullable();
            $table->string('fair_media_video')->nullable();
            $table->string('fair_media_')->nullable();
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
        Schema::dropIfExists('fair_media');
    }
}
