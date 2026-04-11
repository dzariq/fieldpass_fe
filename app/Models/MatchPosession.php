<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class MatchPosession extends Model
{
    protected $table = 'match_posession';

    protected $fillable = [
        'match_id',
        'club_id',
        'event_at',
        'admin_id',
    ];

    protected $casts = [
        'event_at' => 'datetime',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(Matches::class, 'match_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * @return array{
     *     home_seconds: int,
     *     away_seconds: int,
     *     unknown_seconds: int,
     *     home_pct: float|null,
     *     away_pct: float|null
     * }
     */
    public static function summarizeForMatch(Matches $match): array
    {
        $homeId = (int) $match->home_club_id;
        $awayId = (int) $match->away_club_id;
        $started = $match->started_at;
        $rows = static::query()->where('match_id', $match->id)->orderBy('event_at')->get();

        $homeSec = 0;
        $awaySec = 0;
        $unknownSec = 0;

        /*
         * Open-ended intervals must not use raw wall-clock "now" while the match timer is paused:
         * otherwise possession seconds keep growing even though playingElapsedSeconds() is frozen.
         * Cap the timeline at timer_pause_started_at when a pause is active.
         *
         * Note: intervals between two logged events still use wall time; pauses fully inside such
         * an interval are not subtracted unless we store per-pause ranges (future improvement).
         */
        $now = Carbon::now();
        $effectiveEnd = $match->timer_pause_started_at
            ? $match->timer_pause_started_at->copy()
            : $now->copy();

        if ($started && $rows->isNotEmpty()) {
            $firstAt = $rows->first()->event_at;
            if ($firstAt->gt($started)) {
                $unknownEnd = $firstAt->lt($effectiveEnd) ? $firstAt : $effectiveEnd;
                if ($unknownEnd->gt($started)) {
                    $unknownSec = (int) $started->diffInSeconds($unknownEnd);
                }
            }
        }

        foreach ($rows as $i => $row) {
            $from = $row->event_at;
            $next = $rows[$i + 1] ?? null;
            $to = $next ? $next->event_at : $effectiveEnd;
            if ($from >= $to) {
                continue;
            }
            $seconds = (int) $from->diffInSeconds($to);
            $cid = (int) $row->club_id;
            if ($cid === $homeId) {
                $homeSec += $seconds;
            } elseif ($cid === $awayId) {
                $awaySec += $seconds;
            }
        }

        $tracked = $homeSec + $awaySec;
        $totalForPct = $tracked > 0 ? $tracked : 0;

        return [
            'home_seconds' => $homeSec,
            'away_seconds' => $awaySec,
            'unknown_seconds' => $unknownSec,
            'home_pct' => $totalForPct > 0 ? round(100 * $homeSec / $totalForPct, 1) : null,
            'away_pct' => $totalForPct > 0 ? round(100 * $awaySec / $totalForPct, 1) : null,
        ];
    }
}
