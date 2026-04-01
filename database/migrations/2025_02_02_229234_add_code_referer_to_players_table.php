<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCodeRefererToPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('players', 'code')) {
            Schema::table('players', function (Blueprint $table) {
                // Add the 'status' column as ENUM with the desired values
                $table->string('code', 200)->nullable();
                $table->integer('referer')->nullable();
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
        Schema::table('players', function (Blueprint $table) {
            // Drop the 'status' column when rolling back
        });
    }
}
