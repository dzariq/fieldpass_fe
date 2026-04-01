<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Competition extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'competition';

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
    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'competition_club', 'competition_id', 'club_id')->withPivot('status');
    }

     public function fantasyTimelines()
    {
        return $this->hasMany(FantasyTimeline::class, 'competition_id', 'id');
    }

   public function fantasyPoints()
    {
        return $this->hasMany(FantasyPoints::class, 'competition_id', 'id');
    }

    /**
     * Get the fantasy rules for this competition
     */
    public function fantasyRules()
    {
        return $this->hasOne(FantasyRules::class, 'competition_id', 'id');
    }

    public function association()
    {
       return $this->belongsTo(Association::class, 'association_id');
    }

    public function matches()
    {
        return $this->hasMany(Matches::class);
    }
   
}
