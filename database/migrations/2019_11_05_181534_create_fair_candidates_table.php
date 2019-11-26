<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFairCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fair_candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('candidate_id');
            $table->integer('fair_id');
            $table->string('status');
            $table->tinyInteger('agenda')->default(0);
            $table->tinyInteger('is_take_test')->default(0);
            $table->tinyInteger('presenter')->default(0);
            $table->string('marketing_channel');
            $table->string('source');
            $table->tinyInteger('mainhall')->default(0);
            $table->tinyInteger('email_notification')->default(0);
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
        Schema::dropIfExists('fair_candidates');
    }
}
