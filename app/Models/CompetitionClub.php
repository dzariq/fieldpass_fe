<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class CompetitionClub extends Model
{
    protected $fillable = [
        'status', 'club_id', 'competition_id',
    ];
    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'competition_club';
 
    public function competition()
    {
       return $this->belongsTo(Competition::class, 'competition_id');
    }

    public function club()
    {
       return $this->belongsTo(Club::class, 'club_id');
    }
}