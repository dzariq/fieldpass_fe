<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class Matches extends Model
{

    /**
     * Set the default guard for this model.
     *
     * @var string
     */
    protected $table = 'match';

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
    public function home_club()
    {
        return $this->belongsTo(Club::class, 'home_club_id');
    }

    public function away_club()
    {
        return $this->belongsTo(Club::class, 'away_club_id');
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class, 'competition_id');
    }

    // Custom Scopes (these were missing!)
    public function scopeCompleted($query)
    {
        return $query->where('status', 'END');
    }

    public function scopeNotstarted($query)
    {
        return $query->where('status', 'NOT_STARTED');
    }

    // This is the missing scope that was causing the error
    public function scopeForAssociation($query, $associationId)
    {
        return $query->whereHas('home_club', function ($q) use ($associationId) {
            $q->where('association_id', $associationId);
        });
    }

    public function scopePassed($query)
    {
        return $query->where('date', '<', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }
}
