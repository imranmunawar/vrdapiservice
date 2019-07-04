<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('fair_id')->unsigned()->nullable();
            $table->string('user_ip')->nullable();
            $table->string('location')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('referrer')->nullable();
            $table->string('u_id')->nullable();
            $table->timestamp('expiry_date');
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
        Schema::dropIfExists('user_logs');
    }
}
