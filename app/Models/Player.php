<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class Player extends Authenticatable implements Auditable
{
    use Notifiable, HasRoles;
    use \OwenIt\Auditing\Auditable;

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $guard_name = 'player';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'identity_number',
        'phone',
        'status',
        'email',
        'password',
        'jersey_number',
        'country_code',
        'avatar',
        'market_value',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'player_club');
    }
    public function contracts()
    {
        return $this->hasMany(PlayerContract::class);
    }
}
