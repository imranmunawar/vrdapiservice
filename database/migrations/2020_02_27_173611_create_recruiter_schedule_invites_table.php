<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecruiterScheduleInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiter_schedule_invites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('u_id');
            $table->integer('fair_id');
            $table->integer('recruiter_id');
            $table->integer('candidate_id');
            $table->integer('slot_id');
            $table->string('status')->default('pending');
            $table->longText('notes')->nullable();
            $table->tinyInteger('expire')->default(0);
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
        Schema::dropIfExists('recruiter_schedule_invites');
    }
}
