<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchPlayersTable extends Migration
{
    public function up()
    {
        Schema::create('match_players', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id');
            $table->unsignedBigInteger('club_id');
            $table->string('code');

            // Starting 11
            $table->unsignedBigInteger('gk')->nullable();
            for ($i = 1; $i <= 10; $i++) {
                $table->unsignedBigInteger("player{$i}")->nullable();
            }

            // Substitutes
            for ($i = 1; $i <= 7; $i++) {
                $table->unsignedBigInteger("sub{$i}")->nullable();
            }

            $table->timestamps();

            // Foreign key constraint
            $table->foreign('match_id')->references('id')->on('match')->onDelete('cascade');
            $table->foreign('club_id')->references('id')->on('club')->onDelete('cascade');

            // Optional: add foreign keys for players if needed (can slow inserts)
            $table->foreign('gk')->references('id')->on('players')->onDelete('set null');
            for ($i = 1; $i <= 10; $i++) {
                $table->foreign("player{$i}")->references('id')->on('players')->onDelete('set null');
            }
            for ($i = 1; $i <= 7; $i++) {
                $table->foreign("sub{$i}")->references('id')->on('players')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('match_players');
    }
}
