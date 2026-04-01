<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PlayerTraining extends Model
{
    use HasFactory;

    protected $table = 'player_training';

    protected $fillable = [
        'player_id',
        'training_attribute_id',
        'start_date',
        'end_date',
        'score',
        'message',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'score' => 'decimal:2',
    ];

    /**
     * Get the player that owns the training.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the training attribute.
     */
    public function trainingAttribute(): BelongsTo
    {
        return $this->belongsTo(TrainingAttribute::class);
    }

    /**
     * Check if the training period is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->end_date->isPast() && is_null($this->score);
    }

    /**
     * Check if the training is active (within the training period).
     */
    public function isActive(): bool
    {
        $today = Carbon::today();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the training is completed (has a score).
     */
    public function isCompleted(): bool
    {
        return !is_null($this->score);
    }

    /**
     * Get the status of the training.
     */
    public function getStatus(): string
    {
        if ($this->isCompleted()) {
            return 'completed';
        }
        
        if ($this->isOverdue()) {
            return 'overdue';
        }
        
        if ($this->isActive()) {
            return 'active';
        }
        
        return 'upcoming';
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->getStatus()) {
            'completed' => 'success',
            'overdue' => 'danger',
            'active' => 'warning',
            'upcoming' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Scope a query to only include overdue trainings.
     */
    public function scopeOverdue($query)
    {
        return $query->where('end_date', '<', Carbon::today())
                    ->whereNull('score');
    }

    /**
     * Scope a query to only include active trainings.
     */
    public function scopeActive($query)
    {
        $today = Carbon::today();
        return $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
    }

    /**
     * Scope a query to only include completed trainings.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('score');
    }
}