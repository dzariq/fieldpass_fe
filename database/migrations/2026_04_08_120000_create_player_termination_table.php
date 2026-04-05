<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_termination')) {
            return;
        }

        Schema::create('player_termination', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('club')->cascadeOnDelete();
            $table->text('remark');
            $table->dateTime('terminated_at');
            $table->foreignId('admin_id')->constrained('admins')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_termination');
    }
};
