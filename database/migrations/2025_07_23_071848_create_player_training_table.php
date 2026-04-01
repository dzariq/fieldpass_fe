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
        Schema::create('player_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->foreignId('training_attribute_id')->constrained('training_attributes')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            
            // Add indexes for better query performance
            $table->index(['player_id', 'training_attribute_id']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_training');
    }
};