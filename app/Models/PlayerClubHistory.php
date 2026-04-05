<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class PlayerClubHistory extends Model
{
    protected $table = 'player_club_history';

    protected $fillable = [
        'player_id',
        'club_id',
        'event_type',
        'event_at',
        'admin_id',
        'remark',
    ];

    protected $casts = [
        'event_at' => 'datetime',
    ];

    public static function record(
        int $playerId,
        int $clubId,
        string $eventType,
        ?int $adminId = null,
        ?string $remark = null,
        ?Carbon $eventAt = null,
    ): void {
        if (! Schema::hasTable('player_club_history')) {
            return;
        }

        if (! in_array($eventType, ['assigned', 'terminated', 'removed'], true)) {
            throw new \InvalidArgumentException('Invalid player club history event_type.');
        }

        self::query()->create([
            'player_id' => $playerId,
            'club_id' => $clubId,
            'event_type' => $eventType,
            'event_at' => $eventAt ?? now(),
            'admin_id' => $adminId,
            'remark' => $remark,
        ]);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
