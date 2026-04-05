<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matches extends Model
{
    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'match';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    public function home_club()
    {
        return $this->belongsTo(Club::class, 'home_club_id');
    }

    public function away_club()
    {
        return $this->belongsTo(Club::class, 'away_club_id');
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function possessions()
    {
        return $this->hasMany(MatchPosession::class, 'match_id')->orderBy('event_at');
    }

    protected $casts = [
        'started_at' => 'datetime',
        'timer_pause_started_at' => 'datetime',
    ];

    /**
     * Match clock elapsed seconds (wall time since kickoff minus paused intervals).
     */
    public function playingElapsedSeconds(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        $now = now();
        $raw = (int) $this->started_at->diffInSeconds($now);
        $pausedStored = (int) ($this->timer_paused_seconds ?? 0);
        $currentPause = 0;
        if ($this->timer_pause_started_at) {
            $currentPause = (int) $this->timer_pause_started_at->diffInSeconds($now);
        }

        return max(0, $raw - $pausedStored - $currentPause);
    }

    // Custom Scopes (these were missing!)
    public function scopeCompleted($query)
    {
        return $query->where('status', 'END');
    }

    public function scopeNotstarted($query)
    {
        return $query->where('status', 'NOT_STARTED');
    }

    // This is the missing scope that was causing the error
    public function scopeForAssociation($query, $associationId)
    {
        return $query->whereHas('home_club', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        });
    }

    public function scopePassed($query)
    {
        return $query->where('date', '<', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }
}
