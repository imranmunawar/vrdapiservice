<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCometChatProsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('comet_chat_pros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('organizer_id');
            $table->string('app_id');
            $table->string('api_key');
            $table->string('rest_api_key');
            $table->string('region')->default('eu');
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
        Schema::dropIfExists('comet_chat_pros');
    }
}
