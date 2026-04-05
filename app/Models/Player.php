<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Traits\HasRoles;

class Player extends Authenticatable implements Auditable
{
    use HasRoles, Notifiable;
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
        'identity_type',
        'position',
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

    protected static function booted(): void
    {
        static::saving(function (Player $player): void {
            $player->identity_type = self::inferIdentityTypeFromIdentityNumber($player->identity_number);
        });
    }

    /**
     * Malaysia IC is stored as XXXXXX-XX-XXXX; anything else is treated as foreign ID.
     */
    public static function inferIdentityTypeFromIdentityNumber(?string $identityNumber): string
    {
        $num = trim((string) $identityNumber);

        if ($num !== '' && preg_match('/^\d{6}-\d{2}-\d{4}$/', $num)) {
            return 'malaysia_ic';
        }

        return 'foreign_id';
    }

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'player_club');
    }

    public function contracts()
    {
        return $this->hasMany(PlayerContract::class);
    }

    public function terminations()
    {
        return $this->hasMany(PlayerTermination::class, 'player_id');
    }

    public function clubHistories()
    {
        return $this->hasMany(PlayerClubHistory::class, 'player_id')->orderByDesc('event_at');
    }
}
