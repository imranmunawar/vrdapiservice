<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatTranscriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_transcripts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sender_id');
            $table->integer('receiver_id');
            $table->string('category');
            $table->string('type');
            $table->string('sender_role');
            $table->string('receiver_role');
            $table->string('sender_name');
            $table->string('receiver_name');
            $table->string('sender_avatar');
            $table->string('receiver_avatar');
            $table->longText('message');
            $table->string('extension')->nullable();
            $table->string('sent_at');
            $table->integer('fair_id');
            $table->integer('company_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_transcripts');
    }
}
