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
        'playing_elapsed_seconds',
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
     *     neutral_seconds: int,
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
        $neutralSec = 0;
        $unknownSec = 0;

        $allHavePlaying = $rows->isNotEmpty()
            && $rows->every(fn (self $r) => $r->playing_elapsed_seconds !== null);

        if ($allHavePlaying) {
            $currentPlaying = $match->playingElapsedSeconds();
            if ($currentPlaying === null) {
                $currentPlaying = 0;
            }

            $first = $rows->first();
            $unknownSec = max(0, (int) $first->playing_elapsed_seconds);

            foreach ($rows as $i => $row) {
                $fromPlay = (int) $row->playing_elapsed_seconds;
                $nextRow = $rows[$i + 1] ?? null;
                $toPlay = $nextRow !== null
                    ? (int) $nextRow->playing_elapsed_seconds
                    : $currentPlaying;
                $seconds = max(0, $toPlay - $fromPlay);
                $cid = $row->club_id !== null ? (int) $row->club_id : null;
                if ($cid === $homeId) {
                    $homeSec += $seconds;
                } elseif ($cid === $awayId) {
                    $awaySec += $seconds;
                } elseif ($cid === null) {
                    $neutralSec += $seconds;
                }
            }
        } else {
            /*
             * Legacy: wall-clock segments (inaccurate across pauses). Cap open end at pause start
             * so the live tail does not grow while the clock is paused.
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
                if (! $from->lt($to)) {
                    continue;
                }
                $seconds = (int) $from->diffInSeconds($to);
                $cid = $row->club_id !== null ? (int) $row->club_id : null;
                if ($cid === $homeId) {
                    $homeSec += $seconds;
                } elseif ($cid === $awayId) {
                    $awaySec += $seconds;
                } elseif ($cid === null) {
                    $neutralSec += $seconds;
                }
            }
        }

        $tracked = $homeSec + $awaySec;
        $totalForPct = $tracked > 0 ? $tracked : 0;

        return [
            'home_seconds' => $homeSec,
            'away_seconds' => $awaySec,
            'neutral_seconds' => $neutralSec,
            'unknown_seconds' => $unknownSec,
            'home_pct' => $totalForPct > 0 ? round(100 * $homeSec / $totalForPct, 1) : null,
            'away_pct' => $totalForPct > 0 ? round(100 * $awaySec / $totalForPct, 1) : null,
        ];
    }
}
