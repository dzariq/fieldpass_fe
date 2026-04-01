<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Club extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'club';

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
  
     public function association()
     {
        return $this->belongsTo(Association::class, 'association_id');
     }

     public function competitions()
     {
         return $this->belongsToMany(Competition::class, 'competition_club', 'club_id', 'competition_id');
     }

     public function admins()
     {
         return $this->belongsToMany(Admin::class, 'admin_club', 'club_id', 'admin_id');
     }
   
}
