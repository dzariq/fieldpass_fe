<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\MatchPosession;
use App\Models\Player;
use App\Services\MatchN8nLineupService;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MatchUpdateController extends Controller
{
    public function match_info(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        if (auth()->user()->can('association.view')) {
            return view('backend.pages.players.index', [
                'players' => Player::all(),
            ]);
        } else {

            $existingLineup = null;
            $matchEvents = [];
            $match_id = request()->get('id');

            // get upcoming match

            $upcomingFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->where('match.id', $match_id)
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'match.home_club_id',
                    'match.away_club_id',
                    'match.home_score',
                    'match.away_score',
                    'match.started_at',
                    'match.status',
                    'home_club.name as home_club_name',
                    'home_club.avatar as home_club_avatar',
                    'away_club.name as away_club_name',
                    'away_club.avatar as away_club_avatar',
                    'competition.name as competition_name',
                    'competition.type as competition_type',
                    'competition.id as competition_id'
                )
                ->orderBy('match.date', 'asc')
                ->limit(1)
                ->get();

            $matchData = count($upcomingFixtures) > 0 ? $upcomingFixtures[0] : null;
            $awayLineup = [];
            $homeLineup = [];

            if ($matchData) {
                // Fetch existing match events
                $matchEvents = DB::table('match_events')
                    ->join('players', 'match_events.player_id', '=', 'players.id')
                    ->join('club', 'match_events.club_id', '=', 'club.id')
                    ->where('match_events.match_id', $matchData->id)
                    ->select(
                        'match_events.event_id',
                        'match_events.player_id',
                        'match_events.event_type',
                        'match_events.minute_in_match',
                        'club.name as club_name',
                        'players.name as player_name',

                    )
                    ->orderBy('match_events.minute_in_match', 'asc')
                    ->get()
                    ->map(function ($event) {
                        return $event;
                    });

                $awayLineup = MatchPlayer::where('match_id', $matchData->id)
                    ->where('club_id', $matchData->away_club_id)
                    ->first();

                $homeLineup = MatchPlayer::where('match_id', $matchData->id)
                    ->where('club_id', $matchData->home_club_id)
                    ->first();
            }

            // Get Home Team Lineup
            $existingLineupHome = MatchPlayer::where('match_id', $matchData->id)
                ->where('club_id', $matchData->home_club_id)
                ->first();

            // Get Away Team Lineup
            $existingLineupAway = MatchPlayer::where('match_id', $matchData->id)
                ->where('club_id', $matchData->away_club_id)
                ->first();

            // Helper function to extract player IDs from lineup
            $getPlayerIds = function ($lineup) {
                if (! $lineup) {
                    return [];
                }

                return array_filter([
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
            };

            $homePlayerIds = $getPlayerIds($existingLineupHome);
            $awayPlayerIds = $getPlayerIds($existingLineupAway);

            // Get Home Team Lineup
            $existingLineupHome = MatchPlayer::where('match_id', $matchData->id)
                ->where('club_id', $matchData->home_club_id)
                ->first();

            // Get Away Team Lineup
            $existingLineupAway = MatchPlayer::where('match_id', $matchData->id)
                ->where('club_id', $matchData->away_club_id)
                ->first();

            // Extract Home Team Player IDs
            $homePlayerIds = [];
            if ($existingLineupHome) {
                $homePlayerIds = array_filter([
                    $existingLineupHome->gk,
                    $existingLineupHome->player1,
                    $existingLineupHome->player2,
                    $existingLineupHome->player3,
                    $existingLineupHome->player4,
                    $existingLineupHome->player5,
                    $existingLineupHome->player6,
                    $existingLineupHome->player7,
                    $existingLineupHome->player8,
                    $existingLineupHome->player9,
                    $existingLineupHome->player10,
                    $existingLineupHome->sub1,
                    $existingLineupHome->sub2,
                    $existingLineupHome->sub3,
                    $existingLineupHome->sub4,
                    $existingLineupHome->sub5,
                    $existingLineupHome->sub6,
                    $existingLineupHome->sub7,
                    $existingLineupHome->sub8,
                    $existingLineupHome->sub9,
                ]);
            }

            // Extract Away Team Player IDs
            $awayPlayerIds = [];
            if ($existingLineupAway) {
                $awayPlayerIds = array_filter([
                    $existingLineupAway->gk,
                    $existingLineupAway->player1,
                    $existingLineupAway->player2,
                    $existingLineupAway->player3,
                    $existingLineupAway->player4,
                    $existingLineupAway->player5,
                    $existingLineupAway->player6,
                    $existingLineupAway->player7,
                    $existingLineupAway->player8,
                    $existingLineupAway->player9,
                    $existingLineupAway->player10,
                    $existingLineupAway->sub1,
                    $existingLineupAway->sub2,
                    $existingLineupAway->sub3,
                    $existingLineupAway->sub4,
                    $existingLineupAway->sub5,
                    $existingLineupAway->sub6,
                    $existingLineupAway->sub7,
                    $existingLineupAway->sub8,
                    $existingLineupAway->sub9,
                ]);
            }

            // Get Home Team Players with details (ONLY those in lineup)
            $homePlayers = count($homePlayerIds) > 0
                ? Player::select(['id', 'jersey_number', 'name', 'position', 'identity_number', 'email'])
                    ->whereIn('id', $homePlayerIds)
                    ->orderBy('name', 'asc')
                    ->get()
                : collect();

            // Get Away Team Players with details (ONLY those in lineup)
            $awayPlayers = count($awayPlayerIds) > 0
                ? Player::select(['id', 'jersey_number', 'name', 'position', 'identity_number', 'email'])
                    ->whereIn('id', $awayPlayerIds)
                    ->orderBy('name', 'asc')
                    ->get()
                : collect();

            $possessionMatch = null;
            $possessionSummary = [
                'home_seconds' => 0,
                'away_seconds' => 0,
                'neutral_seconds' => 0,
                'unknown_seconds' => 0,
                'home_pct' => null,
                'away_pct' => null,
            ];
            if ($matchData) {
                $possessionMatch = Matches::query()
                    ->with(['home_club', 'away_club', 'possessions.club', 'possessions.admin'])
                    ->find($matchData->id);
                if ($possessionMatch) {
                    $possessionSummary = MatchPosession::summarizeForMatch($possessionMatch);
                }

                $this->updateMatchScores($matchData->id);
                $scoreRow = DB::table('match')->where('id', $matchData->id)->select('home_score', 'away_score')->first();
                if ($scoreRow) {
                    $matchData->home_score = $scoreRow->home_score;
                    $matchData->away_score = $scoreRow->away_score;
                }
            }

            $substitutionPlayerOutIdsHome = [];
            $substitutionPlayerInIdsHome = [];
            $substitutionPlayerOutIdsAway = [];
            $substitutionPlayerInIdsAway = [];
            if ($matchData) {
                [$substitutionPlayerOutIdsHome, $substitutionPlayerInIdsHome] = $this->substitutionSelectIdsForClub(
                    (int) $matchData->id,
                    (int) $matchData->home_club_id,
                    $existingLineupHome
                );
                [$substitutionPlayerOutIdsAway, $substitutionPlayerInIdsAway] = $this->substitutionSelectIdsForClub(
                    (int) $matchData->id,
                    (int) $matchData->away_club_id,
                    $existingLineupAway
                );
            }

            return view('backend.pages.matches.event', [
                'playersHome' => $homePlayers,
                'playersAway' => $awayPlayers,
                'opponentTeamName' => null,
                'match' => $matchData,
                'existingLineupHome' => $homeLineup ? $homeLineup : null,
                'existingLineupAway' => $awayLineup ? $awayLineup : null,
                'matchEvents' => $matchEvents ? $matchEvents : [],
                'possessionMatch' => $possessionMatch,
                'possessionSummary' => $possessionSummary,
                'substitutionPlayerOutIdsHome' => $substitutionPlayerOutIdsHome,
                'substitutionPlayerInIdsHome' => $substitutionPlayerInIdsHome,
                'substitutionPlayerOutIdsAway' => $substitutionPlayerOutIdsAway,
                'substitutionPlayerInIdsAway' => $substitutionPlayerInIdsAway,
            ]);
        }
    }

    public function event_save(Request $request): RedirectResponse|JsonResponse
    {
        if ($this->requestWantsSingleEventJson($request)) {
            return $this->eventSaveSingleJson($request);
        }

        try {
            $matchId = $request->input('match_id');
            $lineupId = $request->input('lineup_id');
            $clubId = $request->input('club_id'); // Get club_id from request (home or away)
            $createdBy = auth()->user()->username ?? auth()->user()->email ?? 'admin';

            // Get match data to calculate timestamps
            $match = DB::table('match')->where('id', $matchId)->first();

            if (! $match) {
                return back()->withErrors(['error' => '❌ Match not found.'])->withInput();
            }

            $matchStartTime = Carbon::createFromTimestamp($match->date);
            $eventsCreated = 0;
            $eventsFailed = 0;

            // Process Goals
            if ($request->has('goals')) {
                foreach ($request->input('goals') as $goal) {
                    if (! empty($goal['player_id']) && ! empty($goal['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $goal['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $goal['player_id'],
                                'event_type' => 'goal',
                                'event_timestamp' => time(),
                                'minute_in_match' => $goal['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save goal event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Assists
            if ($request->has('assists')) {
                foreach ($request->input('assists') as $assist) {
                    if (! empty($assist['player_id']) && ! empty($assist['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $assist['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $assist['player_id'],
                                'event_type' => 'assist',
                                'event_timestamp' => time(),
                                'minute_in_match' => $assist['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save assist event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Substitutions
            if ($request->has('substitutions')) {
                foreach ($request->input('substitutions') as $substitution) {
                    if (! empty($substitution['player_out_id']) && ! empty($substitution['player_in_id']) && ! empty($substitution['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $substitution['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $substitution['player_out_id'],
                                'event_type' => 'sub_out',
                                'event_timestamp' => time(),
                                'minute_in_match' => $substitution['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $substitution['player_in_id'],
                                'event_type' => 'sub_in',
                                'event_timestamp' => time(),
                                'minute_in_match' => $substitution['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save substitution event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Yellow Cards
            if ($request->has('yellow_cards')) {
                foreach ($request->input('yellow_cards') as $yellowCard) {
                    if (! empty($yellowCard['player_id']) && ! empty($yellowCard['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $yellowCard['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $yellowCard['player_id'],
                                'event_type' => 'yellow_card',
                                'event_timestamp' => time(),
                                'minute_in_match' => $yellowCard['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save yellow card event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Red Cards
            if ($request->has('red_cards')) {
                foreach ($request->input('red_cards') as $redCard) {
                    if (! empty($redCard['player_id']) && ! empty($redCard['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $redCard['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $redCard['player_id'],
                                'event_type' => 'red_card',
                                'event_timestamp' => time(),
                                'minute_in_match' => $redCard['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save red card event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Penalty Missed
            if ($request->has('penalty_missed')) {
                foreach ($request->input('penalty_missed') as $penaltyMissed) {
                    if (! empty($penaltyMissed['player_id']) && ! empty($penaltyMissed['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $penaltyMissed['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $penaltyMissed['player_id'],
                                'event_type' => 'penalty_missed',
                                'event_timestamp' => time(),
                                'minute_in_match' => $penaltyMissed['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save penalty missed event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Penalty Saved
            if ($request->has('penalty_saved')) {
                foreach ($request->input('penalty_saved') as $penaltySaved) {
                    if (! empty($penaltySaved['player_id']) && ! empty($penaltySaved['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $penaltySaved['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $penaltySaved['player_id'],
                                'event_type' => 'penalty_saved',
                                'event_timestamp' => time(),
                                'minute_in_match' => $penaltySaved['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save penalty saved event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Own Goals
            if ($request->has('own_goals')) {
                foreach ($request->input('own_goals') as $ownGoal) {
                    if (! empty($ownGoal['player_id']) && ! empty($ownGoal['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int) $ownGoal['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $ownGoal['player_id'],
                                'event_type' => 'own_goal',
                                'event_timestamp' => time(),
                                'minute_in_match' => $ownGoal['minute'],
                                'created_by' => $createdBy,
                                'matchweek' => $match->matchweek,
                                'competition_id' => $match->competition_id,
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {

                            Log::error('Failed to save own goal event: '.$e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Update match scores based on all goal events
            // if ($eventsCreated > 0) {
            $this->updateMatchScores($matchId);
            $this->playerUpdate($matchId);
            // }

            // Prepare success message
            $message = "✅ Match events saved successfully! ($eventsCreated events created)";

            if ($eventsFailed > 0) {
                $message .= " ⚠️ $eventsFailed events failed to save.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to save match events: '.$e->getMessage());

            return back()->withErrors(['error' => '❌ Failed to save match events: '.$e->getMessage()])->withInput();
        }
    }

    /**
     * Update match scores based on goal events
     */
    private function updateMatchScores($matchId)
    {
        try {
            // Get match details
            $match = DB::table('match')->where('id', $matchId)->first();

            if (! $match) {
                Log::error("Match not found for score update: {$matchId}");

                return;
            }

            // Count goals for home team (regular goals)
            $homeGoals = DB::table('match_events')
                ->where('match_id', $matchId)
                ->where('club_id', $match->home_club_id)
                ->where('event_type', 'goal')
                ->count();

            // Count goals for away team (regular goals)
            $awayGoals = DB::table('match_events')
                ->where('match_id', $matchId)
                ->where('club_id', $match->away_club_id)
                ->where('event_type', 'goal')
                ->count();

            // Count own goals by home team (adds to away score)
            $homeOwnGoals = DB::table('match_events')
                ->where('match_id', $matchId)
                ->where('club_id', $match->home_club_id)
                ->where('event_type', 'own_goal')
                ->count();

            // Count own goals by away team (adds to home score)
            $awayOwnGoals = DB::table('match_events')
                ->where('match_id', $matchId)
                ->where('club_id', $match->away_club_id)
                ->where('event_type', 'own_goal')
                ->count();

            // Calculate final scores
            // Home score = home goals + away own goals
            $finalHomeScore = $homeGoals + $awayOwnGoals;

            // Away score = away goals + home own goals
            $finalAwayScore = $awayGoals + $homeOwnGoals;

            // Update match table
            DB::table('match')
                ->where('id', $matchId)
                ->update([
                    'home_score' => $finalHomeScore,
                    'away_score' => $finalAwayScore,
                ]);

            Log::info("Match scores updated for match {$matchId}: Home {$finalHomeScore} - {$finalAwayScore} Away");

        } catch (\Exception $e) {
            Log::error('Failed to update match scores: '.$e->getMessage());
        }
    }

    /**
     * Calculate event timestamp based on match start time and minute in match
     *
     * @return int Unix timestamp
     */
    private function calculateEventTimestamp(Carbon $matchStartTime, int $minute): int
    {
        return $matchStartTime->copy()->addMinutes($minute)->timestamp;
    }

    public function event_snapshot(Request $request): JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchId = (int) $request->query('id', 0);
        if ($matchId <= 0) {
            return response()->json(['success' => false, 'message' => 'Missing match id.'], 422);
        }

        $this->updateMatchScores($matchId);

        $matchForView = $this->getMatchForEventView($matchId);
        if (! $matchForView) {
            return response()->json(['success' => false, 'message' => 'Match not found.'], 404);
        }

        $matchEvents = $this->getMatchEventsForDisplay($matchId);

        return response()->json([
            'success' => true,
            'home_score' => (int) ($matchForView->home_score ?? 0),
            'away_score' => (int) ($matchForView->away_score ?? 0),
            'page_title' => $matchForView->home_club_name.' vs '.$matchForView->away_club_name,
            'match_info' => '📅 '.\Carbon\Carbon::createFromTimestamp((int) $matchForView->date)->format('d M Y H:i'),
            'recorded_events_html' => $this->renderRecordedEventsBlockHtml($matchForView, $matchEvents),
        ]);
    }

    private function requestWantsSingleEventJson(Request $request): bool
    {
        if ($request->isJson()) {
            return true;
        }

        $ct = strtolower((string) $request->header('Content-Type', ''));

        return str_contains($ct, 'application/json');
    }

    /**
     * Save one match event via JSON (used by AJAX on the match events page).
     */
    private function eventSaveSingleJson(Request $request): JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $allowedActions = ['goal', 'assist', 'substitution', 'yellow_card', 'red_card', 'penalty_missed', 'penalty_saved', 'own_goal'];

        $validated = $request->validate([
            'match_id' => 'required|integer',
            'club_id' => 'required|integer',
            'lineup_id' => 'nullable',
            'action' => 'required|string|in:'.implode(',', $allowedActions),
            'minute' => 'required|integer|min:1|max:120',
            'player_id' => [
                Rule::requiredIf(fn () => (string) $request->input('action') !== 'substitution'),
                'nullable',
                'integer',
                'min:1',
            ],
            'player_out_id' => [
                Rule::requiredIf(fn () => (string) $request->input('action') === 'substitution'),
                'nullable',
                'integer',
                'min:1',
            ],
            'player_in_id' => [
                Rule::requiredIf(fn () => (string) $request->input('action') === 'substitution'),
                'nullable',
                'integer',
                'min:1',
            ],
        ], [
            'minute.required' => __('The minute field is required and cannot be empty.'),
            'minute.integer' => __('Please enter a valid minute (1–120).'),
            'minute.min' => __('Please enter a valid minute (1–120).'),
            'minute.max' => __('Please enter a valid minute (1–120).'),
            'player_id.required' => __('Please select a player.'),
            'player_id.min' => __('Please select a player.'),
            'player_out_id.required' => __('Please select both players for the substitution.'),
            'player_in_id.required' => __('Please select both players for the substitution.'),
        ]);

        $action = $validated['action'];
        $matchId = (int) $validated['match_id'];
        $clubId = (int) $validated['club_id'];
        $minute = (int) $validated['minute'];

        $match = DB::table('match')->where('id', $matchId)->first();
        if (! $match) {
            return response()->json(['success' => false, 'message' => 'Match not found.'], 404);
        }

        if (! in_array($clubId, [(int) $match->home_club_id, (int) $match->away_club_id], true)) {
            return response()->json(['success' => false, 'message' => 'Invalid club for this match.'], 422);
        }

        $createdBy = auth()->user()->username ?? auth()->user()->email ?? 'admin';
        $eventsCreated = 0;

        try {
            if ($action === 'substitution') {
                $playerOutId = (int) $validated['player_out_id'];
                $playerInId = (int) $validated['player_in_id'];

                DB::transaction(function () use ($match, $clubId, $playerOutId, $playerInId, $minute, $createdBy): void {
                    $this->insertMatchEventRow($match, $clubId, $playerOutId, 'sub_out', $minute, $createdBy);
                    $this->insertMatchEventRow($match, $clubId, $playerInId, 'sub_in', $minute, $createdBy);
                });
                $eventsCreated = 2;
            } else {
                $eventTypeMap = [
                    'goal' => 'goal',
                    'assist' => 'assist',
                    'yellow_card' => 'yellow_card',
                    'red_card' => 'red_card',
                    'penalty_missed' => 'penalty_missed',
                    'penalty_saved' => 'penalty_saved',
                    'own_goal' => 'own_goal',
                ];
                $eventType = $eventTypeMap[$action];
                $this->insertMatchEventRow($match, $clubId, (int) $validated['player_id'], $eventType, $minute, $createdBy);
                $eventsCreated = 1;
            }

            $this->updateMatchScores($matchId);
            $this->playerUpdate($matchId);

            $matchForView = $this->getMatchForEventView($matchId);
            $matchEvents = $this->getMatchEventsForDisplay($matchId);

            $lineupHome = MatchPlayer::query()->where('match_id', $matchId)->where('club_id', (int) $match->home_club_id)->first();
            $lineupAway = MatchPlayer::query()->where('match_id', $matchId)->where('club_id', (int) $match->away_club_id)->first();
            [$subOutHome, $subInHome] = $this->substitutionSelectIdsForClub($matchId, (int) $match->home_club_id, $lineupHome);
            [$subOutAway, $subInAway] = $this->substitutionSelectIdsForClub($matchId, (int) $match->away_club_id, $lineupAway);

            return response()->json([
                'success' => true,
                'message' => __('Event saved.'),
                'events_created' => $eventsCreated,
                'home_score' => (int) ($matchForView->home_score ?? 0),
                'away_score' => (int) ($matchForView->away_score ?? 0),
                'page_title' => $matchForView->home_club_name.' vs '.$matchForView->away_club_name,
                'match_info' => '📅 '.\Carbon\Carbon::createFromTimestamp((int) $matchForView->date)->format('d M Y H:i'),
                'recorded_events_html' => $this->renderRecordedEventsBlockHtml($matchForView, $matchEvents),
                'substitution_lists' => [
                    'home' => ['out' => $subOutHome, 'in' => $subInHome],
                    'away' => ['out' => $subOutAway, 'in' => $subInAway],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('eventSaveSingleJson: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('Failed to save event.').' '.$e->getMessage(),
            ], 500);
        }
    }

    private function getMatchForEventView(int $matchId): ?object
    {
        return DB::table('match')
            ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
            ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
            ->where('match.id', $matchId)
            ->select(
                'match.id',
                'match.date',
                'match.home_score',
                'match.away_score',
                'match.home_club_id',
                'match.away_club_id',
                'home_club.name as home_club_name',
                'away_club.name as away_club_name',
            )
            ->first();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function getMatchEventsForDisplay(int $matchId)
    {
        return DB::table('match_events')
            ->join('players', 'match_events.player_id', '=', 'players.id')
            ->join('club', 'match_events.club_id', '=', 'club.id')
            ->where('match_events.match_id', $matchId)
            ->select(
                'match_events.event_id',
                'match_events.player_id',
                'match_events.event_type',
                'match_events.minute_in_match',
                'club.name as club_name',
                'players.name as player_name',
            )
            ->orderBy('match_events.minute_in_match', 'asc')
            ->get();
    }

    private function renderRecordedEventsBlockHtml(object $match, $matchEvents): string
    {
        if ($matchEvents->isEmpty()) {
            return '';
        }

        return view('backend.pages.matches.partials.recorded-events-block', [
            'match' => $match,
            'matchEvents' => $matchEvents,
        ])->render();
    }

    private function insertMatchEventRow(object $match, int $clubId, int $playerId, string $eventType, int $minute, string $createdBy): void
    {
        DB::table('match_events')->insert([
            'match_id' => $match->id,
            'club_id' => $clubId,
            'player_id' => $playerId,
            'event_type' => $eventType,
            'event_timestamp' => time(),
            'minute_in_match' => $minute,
            'created_by' => $createdBy,
            'matchweek' => $match->matchweek,
            'competition_id' => $match->competition_id,
        ]);
    }

    /**
     * Delete a specific match event
     */
    public function event_delete(Request $request): RedirectResponse
    {
        try {
            $eventId = $request->input('event_id');

            $event = DB::table('match_events')
                ->where('event_id', $eventId)
                ->first();

            if (! $event) {
                return back()->withErrors(['error' => '❌ Event not found.']);
            }

            $matchIdForScore = (int) $event->match_id;

            DB::table('match_events')
                ->where('event_id', $eventId)
                ->delete();

            $this->updateMatchScores($matchIdForScore);
            $this->playerUpdate($matchIdForScore);

            return back()->with('success', '✅ Event deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete match event: '.$e->getMessage());

            return back()->withErrors(['error' => '❌ Failed to delete event: '.$e->getMessage()]);
        }
    }

    private function playerUpdate($matchId): void
    {
        MatchN8nLineupService::notifyPlayerUpdateForMatch((int) $matchId, true);
    }

    /**
     * Who can be selected for substitution: player out = currently on pitch; player in = still on bench.
     * Replays saved sub_out / sub_in events so players who already came on appear under "player out", not "player in".
     *
     * @return array{0: list<int>, 1: list<int>}
     */
    private function substitutionSelectIdsForClub(int $matchId, int $clubId, ?MatchPlayer $lineup): array
    {
        if ($lineup === null) {
            return [[], []];
        }

        $starters = array_values(array_filter(array_map('intval', [
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
        ]), fn (int $id) => $id > 0));

        $subs = array_values(array_filter(array_map('intval', [
            $lineup->sub1,
            $lineup->sub2,
            $lineup->sub3,
            $lineup->sub4,
            $lineup->sub5,
            $lineup->sub6,
            $lineup->sub7,
            $lineup->sub8,
            $lineup->sub9,
        ]), fn (int $id) => $id > 0));

        $onPitch = $starters;
        $onBench = $subs;

        $events = DB::table('match_events')
            ->where('match_id', $matchId)
            ->where('club_id', $clubId)
            ->whereIn('event_type', ['sub_out', 'sub_in'])
            ->orderBy('minute_in_match')
            ->orderBy('event_id')
            ->get(['event_type', 'player_id']);

        foreach ($events as $ev) {
            $pid = (int) $ev->player_id;
            if ($ev->event_type === 'sub_out') {
                $onPitch = array_values(array_filter($onPitch, fn (int $id) => $id !== $pid));
            } elseif ($ev->event_type === 'sub_in') {
                $onPitch[] = $pid;
                $onBench = array_values(array_filter($onBench, fn (int $id) => $id !== $pid));
            }
        }

        $onPitch = array_values(array_unique($onPitch));
        $onBench = array_values(array_unique($onBench));

        return [$onPitch, $onBench];
    }
}
