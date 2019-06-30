<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareerTestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_tests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('fair_id')->unsigned()->nullable();
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->string('question');
            $table->string('short_question');
            $table->string('backoffice_question')->nullable();
            $table->string('question_type')->nullable();
            $table->string('min_selection')->nullable();
            $table->string('max_selection')->nullable();
            $table->string('display_order')->nullable();
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
        Schema::dropIfExists('career_tests');
    }
}
