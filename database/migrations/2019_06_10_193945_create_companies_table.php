<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('admin_id')->unsigned()->nullable();
            $table->foreign('admin_id')->references('id')->on('users');
            $table->integer('fair_id')->unsigned()->nullable();
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->string('company_name');
            $table->string('company_email')->unique();
            $table->integer('company_post_code')->nullable();
            $table->string('company_state')->nullable();
            $table->string('company_country')->nullable();
            $table->string('company_match')->default(0);
            $table->string('company_web_url')->nullable();
            $table->string('company_facebook_url')->nullable();
            $table->string('company_youtube_url')->nullable();
            $table->string('company_twitter_url')->nullable();
            $table->string('company_in_url')->nullable();
            $table->string('company_instagram_url')->nullable();
            $table->string('company_stand_type')->nullable();
            $table->string('company_logo', 70)->nullable();
            $table->string('company_stand_image', 70)->nullable();
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
        Schema::dropIfExists('companies');
    }
}
