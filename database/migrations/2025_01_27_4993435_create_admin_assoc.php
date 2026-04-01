<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminAssoc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_association', function (Blueprint $table) {
            $table->id();
            $table->integer('admin_id');
            $table->integer('association_id');
            $table->timestamps();
        });

        Schema::create('admin_club', function (Blueprint $table) {
            $table->id();
            $table->integer('admin_id');
            $table->integer('club_id');
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
