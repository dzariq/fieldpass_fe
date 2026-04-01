<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatches extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('match', function (Blueprint $table) {
            $table->id();
            $table->integer('competition_id');
            $table->integer('home_club_id');
            $table->integer('away_club_id');
            $table->integer('date');
            $table->enum('status', ['NOT_STARTED', 'ONGOING','END','POSTPONED'])->default('NOT_STARTED');
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
    }
}
