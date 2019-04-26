<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFairSociallinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fair_sociallinks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fair_id')->unsigned();
            $table->integer('sociallinks_id')->unsigned();
            $table->string('link_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fair_sociallinks');
    }
}
