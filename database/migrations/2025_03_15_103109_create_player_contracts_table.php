<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('player_contracts')) {
            Schema::create('player_contracts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
                $table->foreignId('club_id')->constrained('clubs')->onDelete('cascade');
                $table->date('start_date');
                $table->date('end_date');
                $table->decimal('salary', 12, 2);
                $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
                $table->timestamps();
            });
       }
    }

    public function down()
    {
        Schema::dropIfExists('player_contracts');
    }
}
