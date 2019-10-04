<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatchWebinarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match_webinars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('webinar_id');
            $table->foreign('webinar_id')->references('id')->on('company_webinars');
            $table->integer('candidate_id');
            $table->integer('company_id');
            $table->integer('fair_id');
            $table->integer('percentage');
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
        Schema::dropIfExists('match_webinars');
    }
}
