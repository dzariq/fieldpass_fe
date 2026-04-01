<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemoDataRunsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('demo_data_runs')) {
            Schema::create('demo_data_runs', function (Blueprint $table) {
                $table->id();
                $table->string('key')->index();
                $table->boolean('enabled')->default(false)->index();
                $table->unsignedBigInteger('created_by_admin_id')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('demo_data_runs');
    }
}

