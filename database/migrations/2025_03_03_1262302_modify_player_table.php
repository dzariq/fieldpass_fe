<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('players', 'position')) {
            Schema::table('players', function (Blueprint $table) {
                $table->string('full_name', 100);
                $table->string('nationality', 50)->nullable();
                $table->enum('position', ['Goalkeeper', 'Defender', 'Midfielder', 'Forward']);
                $table->integer('height_cm')->nullable();
                $table->integer('weight_kg')->nullable();
                $table->enum('preferred_foot', ['Left', 'Right', 'Both'])->nullable();
                $table->decimal('market_value', 10, 2)->nullable();       
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
