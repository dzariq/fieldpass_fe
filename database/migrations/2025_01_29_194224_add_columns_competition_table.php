<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsCompetitionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('competition', 'type')) {
            Schema::table('competition', function (Blueprint $table) {
                $table->enum('type', ['LEAGUE', 'TOURNAMENT','CUP'])->default('LEAGUE');
                $table->integer('start');
                $table->integer('end');
                $table->integer('max_participants')->default(12);
                $table->string('description')->nullable();
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
    }
}
