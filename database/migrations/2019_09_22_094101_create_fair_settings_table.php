<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFairSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fair_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fair_id');
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->longText('information_text');
            $table->longText('offline_text');
            $table->longText('address');
            $table->longText('terms_conditions');
            $table->longText('privacy_policy');
            $table->longText('fair_news');
            $table->integer('webinar_enable');
            $table->integer('cv_required');
            $table->integer('interview_room');
            $table->integer('seminar');
            $table->integer('video_chat');
            $table->integer('user_vetting');
            $table->integer('limited_access');
            $table->integer('chat_status');
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
        Schema::dropIfExists('fair_settings');
    }
}
