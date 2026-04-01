<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoDataItemsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('demo_data_items')) {
            Schema::create('demo_data_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('run_id')->index();
                $table->string('table_name', 64)->index();
                $table->unsignedBigInteger('record_id')->index();
                $table->timestamps();

                $table->foreign('run_id')->references('id')->on('demo_data_runs')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('demo_data_items');
    }
}

