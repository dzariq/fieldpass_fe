<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('player_club_history')) {
            return;
        }

        Schema::create('player_club_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('club')->cascadeOnDelete();
            $table->string('event_type', 32); // assigned | terminated | removed
            $table->dateTime('event_at');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->index(['player_id', 'event_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_club_history');
    }
};
