<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlayerList extends Model
{
    use HasFactory;

    protected $table = 'match_player_list';

    protected $fillable = [
        'player_id',
        'club_id',
        'checkin_at',
        'match_id',
    ];

    protected $casts = [
        'checkin_at' => 'datetime',
    ];

    /**
     * Get the player that checked in.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the club associated with the checkin.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the match associated with the checkin.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(Matches::class);
    }

    /**
     * Check if player is checked in.
     */
    public function isCheckedIn(): bool
    {
        return !is_null($this->checkin_at);
    }

    /**
     * Scope for checked-in players only.
     */
    public function scopeCheckedIn($query)
    {
        return $query->whereNotNull('checkin_at');
    }

    /**
     * Scope for a specific match.
     */
    public function scopeForMatch($query, $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    /**
     * Scope for a specific club.
     */
    public function scopeForClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }
}