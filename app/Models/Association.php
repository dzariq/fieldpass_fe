<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Association extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'association';

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
        return $this->hasMany(Club::class);
    }

    public function competitions()
    {
        return $this->hasMany(Competition::class);
    }


   
}
