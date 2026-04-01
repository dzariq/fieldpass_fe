<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerSwapRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'match_player_id',
        'player_out_id',
        'player_in_id',
        'position_type',
        'position_number',
        'requested_by',
        'reason',
        'status',
        'reviewed_by',
        'review_notes',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the match associated with the swap request
     */
    public function match()
    {
        return $this->belongsTo(Match::class);
    }

    /**
     * Get the match_players record
     */
    public function matchPlayer()
    {
        return $this->belongsTo(MatchPlayer::class, 'match_player_id');
    }

    /**
     * Get the player being removed
     */
    public function playerOut()
    {
        return $this->belongsTo(Player::class, 'player_out_id');
    }

    /**
     * Get the player being added
     */
    public function playerIn()
    {
        return $this->belongsTo(Player::class, 'player_in_id');
    }

    /**
     * Get the admin who requested the swap
     */
    public function requester()
    {
        return $this->belongsTo(Admin::class, 'requested_by');
    }

    /**
     * Get the admin who reviewed the swap
     */
    public function reviewer()
    {
        return $this->belongsTo(Admin::class, 'reviewed_by');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for a specific match
     */
    public function scopeForMatch($query, $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    /**
     * Check if request is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute()
    {
        $icons = [
            'pending' => '⏳',
            'approved' => '✅',
            'rejected' => '❌'
        ];

        return $icons[$this->status] ?? '•';
    }
}