<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_media', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('company_id')->unsigned();
          $table->foreign('company_id')->references('id')->on('companies');
          $table->string('company_media_name');
          $table->string('company_media_type');
          $table->string('company_media_description')->nullable();
          $table->string('company_media_link')->nullable();
          $table->string('company_media_image')->nullable();
          $table->string('company_media_video')->nullable();
          $table->string('company_media_document')->nullable();
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
        Schema::dropIfExists('company_media');
    }
}
