<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAssociationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('association', 'status')) {
            Schema::table('association', function (Blueprint $table) {
                // Add the 'status' column as ENUM with the desired values
                $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
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
        Schema::table('association', function (Blueprint $table) {
            // Drop the 'status' column when rolling back
        });
    }
}
