<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('match_player_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('club_id')->constrained('club')->onDelete('cascade');
            $table->timestamp('checkin_at')->nullable();
            $table->foreignId('match_id')->constrained('match')->onDelete('cascade');
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['match_id', 'club_id']);
            $table->index(['player_id', 'match_id']);
            $table->index('checkin_at');
            
            // Ensure unique player per match (prevent duplicates)
            $table->unique(['player_id', 'match_id'], 'unique_player_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_player_list');
    }
};