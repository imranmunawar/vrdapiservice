<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCareerTestAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('career_test_answers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('test_id')->unsigned()->nullable();
            $table->foreign('test_id')->references('id')->on('career_tests');
            $table->string('answer');
            $table->integer('score');
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
        Schema::dropIfExists('career_test_answers');
    }
}
