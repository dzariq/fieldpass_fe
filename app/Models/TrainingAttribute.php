<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'club_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the club that owns the training attribute.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the player training records for this attribute.
     */
    public function playerTraining(): HasMany
    {
        return $this->hasMany(PlayerTraining::class);
    }

    /**
     * Scope a query to only include active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive attributes.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Check if the attribute is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the attribute is inactive.
     */
    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }
}