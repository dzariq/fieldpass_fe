<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMatchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('match', 'home_club_score')) {
            Schema::table('match', function (Blueprint $table) {
                $table->integer('home_club_score')->default(0);
                $table->integer('away_club_score')->default(0);
         });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('match', function (Blueprint $table) {
            // Drop the 'status' column when rolling back
        });
    }
}
