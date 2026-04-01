<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FantasyRules extends Model
{
    protected $table = 'fantasy_rules';

    protected $fillable = [
        'competition_id',
        'season',
        'matchweek',
        'matchweeks',
        'credit',
        'transfer',
        'benchboost',
        'wildcard',
        'triple',
        'max_same_club',
        'GK',
        'DF',
        'MF',
        'ST',
    ];

    /**
     * Get the competition that owns the fantasy rules.
     */
    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }
}