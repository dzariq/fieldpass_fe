<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class PlayerContract extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'player_contracts';

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

    public function player()
    {
       return $this->belongsTo(Player::class, 'player_id');
    }

    public function club()
    {
       return $this->belongsTo(Club::class, 'club_id');
    }
   
}
