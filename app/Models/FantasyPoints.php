<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class FantasyPoints extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'fantasy_points';

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

        /**
     * Get the competition this points rule belongs to
     */
    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id', 'id');
    }

   
}
