<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FantasyTimeline extends Model
{
    protected $table = 'fantasy_timeline';

    protected $fillable = [
        'competition_id',
        'matchweek',
        'cutoff_time',
        'max_same_club',
        'transfer',
        'credit',
        'benchboost',
        'wildcard',
        'triple',
    ];

    /**
     * Get the competition that owns the fantasy timeline.
     */
    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }
}