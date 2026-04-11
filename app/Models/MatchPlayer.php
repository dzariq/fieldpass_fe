<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    use HasFactory;

    protected $table = 'match_players';

    protected $fillable = [
        'match_id',
        'club_id',
        'code',
        'gk',
        'player1',
        'player2',
        'player3',
        'player4',
        'player5',
        'player6',
        'player7',
        'player8',
        'player9',
        'player10',
        'sub1',
        'sub2',
        'sub3',
        'sub4',
        'sub5',
        'sub6',
        'sub7',
        'sub8',
        'sub9',
    ];

    // Optional: relationships
    public function match()
    {
        return $this->belongsTo(Matches::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
