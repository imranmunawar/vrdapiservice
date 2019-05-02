<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->default(0);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('company_name', 60)->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('credits')->nullable();
            $table->tinyInteger('reg_notification')->default(0);
            $table->tinyInteger('enable_exhibitor')->default(0);
            $table->longText('user_info')->nullable();
            $table->string('user_title')->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('location')->nullable();
            $table->string('linkedin_profile_link')->nullable();
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
        Schema::dropIfExists('user_settings');
    }
}
