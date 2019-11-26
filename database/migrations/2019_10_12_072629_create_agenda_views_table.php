<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgendaViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agenda_views', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('recruiter_id');
            $table->integer('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('users');
            $table->integer('fair_id');
            $table->foreign('fair_id')->references('id')->on('fairs');
            $table->integer('company_id');
            $table->foreign('company_id')->references('id')->on('companies');
            $table->integer('view');
            $table->integer('percentage');
            $table->integer('shortlisted');
            $table->integer('rejected');
            $table->longText('notes')->nullable();
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
        Schema::dropIfExists('agenda_views');
    }
}
