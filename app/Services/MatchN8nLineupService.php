<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MatchPlayer;
use App\Models\Player;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * n8n webhooks that populate fantasy_player_match_events should only run when the match
 * is ONGOING or END so lineups saved before kickoff do not create fantasy rows.
 */
final class MatchN8nLineupService
{
    public static function statusAllowsFantasyPlayerEventsSync(?string $status): bool
    {
        return in_array($status, ['ONGOING', 'END'], true);
    }

    /**
     * POST confirm_lineup for one club when the match is eligible (ONGOING or END).
     */
    public static function notifyConfirmLineupForClub(int $matchId, int $clubId): void
    {
        $matchData = DB::table('match')
            ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
            ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
            ->join('competition', 'match.competition_id', '=', 'competition.id')
            ->select(
                'match.id',
                'match.date',
                'match.matchweek',
                'match.home_club_id',
                'match.away_club_id',
                'match.status',
                'home_club.name as home_club_name',
                'home_club.avatar as home_club_avatar',
                'away_club.name as away_club_name',
                'away_club.avatar as away_club_avatar',
                'competition.name as competition_name',
                'competition.type as competition_type',
                'competition.id as competition_id'
            )
            ->where('match.id', $matchId)
            ->first();

        if (! $matchData) {
            Log::info('confirm_lineup skipped: match not found', ['match_id' => $matchId, 'club_id' => $clubId]);

            return;
        }

        if (! self::statusAllowsFantasyPlayerEventsSync($matchData->status ?? null)) {
            Log::info('confirm_lineup skipped: match status not eligible for fantasy sync', [
                'match_id' => $matchId,
                'club_id' => $clubId,
                'status' => $matchData->status ?? null,
            ]);

            return;
        }

        $lineup = MatchPlayer::query()
            ->where('match_id', $matchId)
            ->where('club_id', $clubId)
            ->first();

        if (! $lineup) {
            Log::info('confirm_lineup skipped: no match_players row for club', ['match_id' => $matchId, 'club_id' => $clubId]);

            return;
        }

        $playerIds = array_filter([
            $lineup->gk,
            $lineup->player1,
            $lineup->player2,
            $lineup->player3,
            $lineup->player4,
            $lineup->player5,
            $lineup->player6,
            $lineup->player7,
            $lineup->player8,
            $lineup->player9,
            $lineup->player10,
            $lineup->sub1,
            $lineup->sub2,
            $lineup->sub3,
            $lineup->sub4,
            $lineup->sub5,
            $lineup->sub6,
            $lineup->sub7,
            $lineup->sub8,
            $lineup->sub9,
        ]);

        $players = Player::query()->whereIn('id', $playerIds)->get();

        if ($players->isEmpty()) {
            Log::warning('confirm_lineup skipped: lineup has no resolved players', [
                'match_id' => $matchId,
                'club_id' => $clubId,
                'raw_slot_count' => count($playerIds),
            ]);

            return;
        }

        $payload = [
            'match_id' => $lineup->match_id,
            'club_id' => $lineup->club_id,
            'code' => $lineup->code,
            'match_status' => $matchData->status,
            'fantasy_player_events_sync' => true,
            'text' => 'You have been selected to play in the Match: '.$matchData->home_club_name.' vs '.$matchData->away_club_name.' on '.date('jS F Y', (int) $matchData->date).'. Show this QrCode when entering the field',
            'players' => $players->map(function ($player) {
                return [
                    'id' => $player->id,
                    'name' => $player->name,
                    'phone' => $player->phone,
                    'position' => $player->position,
                    'email' => $player->email,
                ];
            })->values(),
        ];

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://n8n.fieldpass.com.my/webhook/confirm_lineup', $payload);

            if (! $response->successful()) {
                Log::warning('confirm_lineup webhook returned error response', [
                    'match_id' => $matchId,
                    'club_id' => $clubId,
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 2000),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('confirm_lineup webhook failed', [
                'match_id' => $matchId,
                'club_id' => $clubId,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * After the match becomes ONGOING (e.g. record start), sync each side that has a saved lineup.
     */
    public static function syncAllSubmittedLineupsForMatch(int $matchId): void
    {
        $matchRow = DB::table('match')->where('id', $matchId)->first();
        if (! $matchRow) {
            Log::info('syncAllSubmittedLineupsForMatch skipped: match not found', ['match_id' => $matchId]);

            return;
        }

        if (! self::statusAllowsFantasyPlayerEventsSync($matchRow->status ?? null)) {
            Log::info('syncAllSubmittedLineupsForMatch skipped: status not ONGOING/END', [
                'match_id' => $matchId,
                'status' => $matchRow->status ?? null,
            ]);

            return;
        }

        foreach ([(int) $matchRow->home_club_id, (int) $matchRow->away_club_id] as $clubId) {
            self::notifyConfirmLineupForClub($matchId, $clubId);
        }
    }

    /**
     * POST https://n8n.fieldpass.com.my/webhook/player_update — n8n uses this for fantasy / downstream sync.
     * When $requireEligibleStatus is true, only runs for match status ONGOING or END (same as match-event saves).
     * Timer start and timer reset pass false so n8n is notified even when status is NOT_STARTED (e.g. after reset).
     */
    public static function notifyPlayerUpdateForMatch(int $matchId, bool $requireEligibleStatus = true): void
    {
        $competitionId = DB::table('match')->where('id', $matchId)->value('competition_id');
        if ($competitionId === null) {
            return;
        }

        if ($requireEligibleStatus) {
            $status = DB::table('match')->where('id', $matchId)->value('status');
            if (! self::statusAllowsFantasyPlayerEventsSync($status)) {
                return;
            }
        }

        $payload = [
            'match_id' => $matchId,
            'competition_id' => (int) $competitionId,
        ];

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post('https://n8n.fieldpass.com.my/webhook/player_update', $payload);

            if (! $response->successful()) {
                Log::warning('player_update webhook returned error response', [
                    'match_id' => $matchId,
                    'status' => $response->status(),
                    'body' => mb_substr($response->body(), 0, 2000),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('player_update webhook failed', [
                'match_id' => $matchId,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
