<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;
use App\Http\Requests\LineupRequest;
use Illuminate\Foundation\Http\FormRequest;

use App\Models\Admin;
use App\Models\MatchPlayer;
use App\Models\Player;
use App\Models\Association;
use DB;
use App\Models\Club;
use App\Models\PlayerContract;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;


class PlayerLineupController extends Controller
{
 public function lineup(Request $request): Renderable
{
    $this->checkAuthorization(auth()->user(), ['admin.view']);

    if (auth()->user()->can('association.view')) {
        return view('backend.pages.players.index', [
            'players' => Player::all(),
        ]);
    } else {

        $existingLineup = null;
        $matchEvents = [];

        $admin_obj = Admin::find(auth()->user()->id);
        $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();
        $clubId = count($clubIds) > 0 ? $clubIds[0] : $request->get('club_id');

        //get upcoming match

        if ($clubId && auth()->user()->can('club.create')) {
            $match_id = $request->get('id');
            $clubId = $request->get('club_id');

            $upcomingFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'match.home_club_id',
                    'match.away_club_id',
                    'home_club.name as home_club_name',
                    'home_club.avatar as home_club_avatar',
                    'away_club.name as away_club_name',
                    'away_club.avatar as away_club_avatar',
                    'competition.name as competition_name',
                    'competition.type as competition_type',
                    'competition.id as competition_id'
                )
                ->where('match.id', $match_id)
                ->limit(1)
                ->get();
        } else {
            $upcomingFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->where(function ($query) use ($clubId) {
                    $query->where('match.home_club_id', $clubId)
                        ->orWhere('match.away_club_id', $clubId);
                })
                ->where('match.date', '>=', time())
                ->where('match.status', 'NOT_STARTED')
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'match.home_club_id',
                    'match.away_club_id',
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
        }

        // Get players and sort alphabetically
        $players = Player::select(['id', 'name', 'position', 'identity_number', 'email'])
            ->whereHas('clubs', function ($query) use ($clubId) {
                $query->whereIn('club_id', [$clubId]);
            })
            ->with(['clubs' => function ($query) {
                $query->withPivot('club_id', 'player_id');
            }])
            ->orderBy('name', 'asc') // Sort alphabetically by name
            ->get();

        $matchData = count($upcomingFixtures) > 0 ? $upcomingFixtures[0] : null;

        if ($matchData) {

            $existingLineup = MatchPlayer::where('match_id', $matchData->id)
                ->where('club_id', $clubId ?? 1)
                ->first();

            // Fetch existing match events
            if ($existingLineup) {
                $matchEvents = DB::table('match_events')
                    ->join('players', 'match_events.player_id', '=', 'players.id')
                    ->where('match_events.match_id', $matchData->id)
                    ->where('match_events.club_id', $clubId)
                    ->select(
                        'match_events.event_id',
                        'match_events.player_id',
                        'match_events.event_type',
                        'match_events.minute_in_match',
                        'players.name as player_name'
                    )
                    ->orderBy('match_events.minute_in_match', 'asc')
                    ->get()
                    ->map(function ($event) {
                        return $event;
                    });
            }
        }

        return view('backend.pages.players.lineup', [
            'players' => $players,
            'club_id' => $clubId,
            'opponentTeamName' => count($upcomingFixtures) > 0 ? ($upcomingFixtures[0]->home_club_id == $clubId ? $upcomingFixtures[0]->away_club_name : $upcomingFixtures[0]->home_club_name) : null,
            'match' => $matchData,
            'existingLineup' => $existingLineup ? $existingLineup : null,
            'matchEvents' => $matchEvents ? $matchEvents : array(),
        ]);
    }
}

    public function event_save(Request $request): RedirectResponse
    {
        try {
            $matchId = $request->input('match_id');
            $lineupId = $request->input('lineup_id');
            $admin_obj = Admin::find(auth()->user()->id);
            $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();
            $clubId = $clubIds[0];
            $createdBy = auth()->user()->username ?? auth()->user()->email ?? 'admin';

            // Get match data to calculate timestamps
            $match = DB::table('match')->where('id', $matchId)->first();

            if (!$match) {
                return back()->withErrors(['error' => '❌ Match not found.'])->withInput();
            }

            $matchStartTime = Carbon::createFromTimestamp($match->date);
            $eventsCreated = 0;
            $eventsFailed = 0;

            // Process Goals
            if ($request->has('goals')) {
                foreach ($request->input('goals') as $goal) {
                    if (!empty($goal['player_id']) && !empty($goal['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$goal['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $goal['player_id'],
                                'event_type' => 'goal',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $goal['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save goal event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Assists
            if ($request->has('assists')) {
                foreach ($request->input('assists') as $assist) {
                    if (!empty($assist['player_id']) && !empty($assist['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$assist['minute']);


                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $assist['player_id'],
                                'event_type' => 'assist',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $assist['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save assist event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            if ($request->has('substitutions')) {
                foreach ($request->input('substitutions') as $substitution) {
                    if (!empty($substitution['player_out_id']) && !empty($substitution['player_in_id']) && !empty($substitution['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$substitution['minute']);


                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $substitution['player_out_id'],
                                'event_type' => 'sub_out',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $substitution['minute'],
                                'created_by' => $createdBy
                            ]);

                            $eventsCreated++;

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $substitution['player_in_id'],
                                'event_type' => 'sub_in',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $substitution['minute'],
                                'created_by' => $createdBy
                            ]);

                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save substitution event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Yellow Cards
            if ($request->has('yellow_cards')) {
                foreach ($request->input('yellow_cards') as $yellowCard) {
                    if (!empty($yellowCard['player_id']) && !empty($yellowCard['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$yellowCard['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $yellowCard['player_id'],
                                'event_type' => 'yellow_card',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $yellowCard['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save yellow card event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Red Cards
            if ($request->has('red_cards')) {
                foreach ($request->input('red_cards') as $redCard) {
                    if (!empty($redCard['player_id']) && !empty($redCard['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$redCard['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $redCard['player_id'],
                                'event_type' => 'red',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $redCard['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save red card event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Penalty Missed
            if ($request->has('penalty_missed')) {
                foreach ($request->input('penalty_missed') as $penaltyMissed) {
                    if (!empty($penaltyMissed['player_id']) && !empty($penaltyMissed['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$penaltyMissed['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $penaltyMissed['player_id'],
                                'event_type' => 'pen_missed',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $penaltyMissed['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save penalty missed event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Penalty Saved
            if ($request->has('penalty_saved')) {
                foreach ($request->input('penalty_saved') as $penaltySaved) {
                    if (!empty($penaltySaved['player_id']) && !empty($penaltySaved['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$penaltySaved['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $penaltySaved['player_id'],
                                'event_type' => 'pen_saved',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $penaltySaved['minute'],

                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save penalty saved event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Process Own Goals
            if ($request->has('own_goals')) {
                foreach ($request->input('own_goals') as $ownGoal) {
                    if (!empty($ownGoal['player_id']) && !empty($ownGoal['minute'])) {
                        try {
                            $eventTimestamp = $this->calculateEventTimestamp($matchStartTime, (int)$ownGoal['minute']);

                            DB::table('match_events')->insert([
                                'match_id' => $matchId,
                                'club_id' => $clubId,
                                'player_id' => $ownGoal['player_id'],
                                'event_type' => 'own_goal',
                                'event_timestamp' => $eventTimestamp,
                                'minute_in_match' => $ownGoal['minute'],
                                'created_by' => $createdBy
                            ]);

                            $this->playerUpdate($goal['player_id'], $matchId);


                            $eventsCreated++;
                        } catch (\Exception $e) {
                            Log::error('Failed to save own goal event: ' . $e->getMessage());
                            $eventsFailed++;
                        }
                    }
                }
            }

            // Prepare success message
            $message = "✅ Match events saved successfully! ($eventsCreated events created)";

            if ($eventsFailed > 0) {
                $message .= " ⚠️ $eventsFailed events failed to save.";
            }

            return redirect()->route('admin.lineup.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to save match events: ' . $e->getMessage());
            return back()->withErrors(['error' => '❌ Failed to save match events: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Calculate event timestamp based on match start time and minute in match
     * 
     * @param Carbon $matchStartTime
     * @param int $minute
     * @return int Unix timestamp
     */
    private function calculateEventTimestamp(Carbon $matchStartTime, int $minute): int
    {
        return $matchStartTime->copy()->addMinutes($minute)->timestamp;
    }

    /**
     * Delete a specific match event
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function event_delete(Request $request): RedirectResponse
    {
        try {
            $eventId = $request->input('event_id');
            $admin_obj = Admin::find(auth()->user()->id);
            $clubIds = $admin_obj->clubs()->pluck('club.id')->toArray();
            $clubId = $clubIds[0];

            // Verify the event belongs to this club before deleting
            $event = DB::table('match_events')
                ->where('event_id', $eventId)
                ->where('club_id', $clubId)
                ->first();

            if (!$event) {
                return back()->withErrors(['error' => '❌ Event not found or unauthorized.']);
            }

            DB::table('match_events')
                ->where('event_id', $eventId)
                ->where('club_id', $clubId)
                ->delete();

            return back()->with('success', '✅ Event deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete match event: ' . $e->getMessage());
            return back()->withErrors(['error' => '❌ Failed to delete event: ' . $e->getMessage()]);
        }
    }

    public function save(LineupRequest $request): RedirectResponse
    {
        $starters = $request->input('starters', []);
        $subs = $request->input('subs', []);

        // Only count real selections (ignore empty strings / nulls)
        $selected = array_values(array_filter(array_merge($starters, $subs), fn ($v) => $v !== null && $v !== ''));

        if (count($selected) < 14) {
            return back()->withErrors(['minimum' => '❌ Please select at least 14 players (11 starters + minimum 3 substitutes).'])->withInput();
        }

        if (count($selected) !== count(array_unique($selected))) {
            return back()->withErrors(['duplicate' => '❌ Player cannot be selected more than once.'])->withInput();
        }

        $clubId = request()->post('club_id');
        $matchId = $request->match_id;
        $matchObj = DB::table('match')->where('id', $matchId)->first();
        $competitionObj = DB::table('competition')->where('id', $matchObj->competition_id)->first();

        // Check if already exists
        $lineup = MatchPlayer::where('match_id', $matchId)
            ->where('club_id', $clubId)
            ->first();

        $code = $lineup->code ?? random_int(10000000, 99999999);

        $data = [
            'match_id' => $matchId,
            'club_id' => $clubId,
            'code' => $code,
            'gk' => $starters[0] ?? null,
            'player1' => $starters[1] ?? null,
            'player2' => $starters[2] ?? null,
            'player3' => $starters[3] ?? null,
            'player4' => $starters[4] ?? null,
            'player5' => $starters[5] ?? null,
            'player6' => $starters[6] ?? null,
            'player7' => $starters[7] ?? null,
            'player8' => $starters[8] ?? null,
            'player9' => $starters[9] ?? null,
            'player10' => $starters[10] ?? null,
            'sub1' => $subs[0] ?? null,
            'sub2' => $subs[1] ?? null,
            'sub3' => $subs[2] ?? null,
            'sub4' => $subs[3] ?? null,
            'sub5' => $subs[4] ?? null,
            'sub6' => $subs[5] ?? null,
            'sub7' => $subs[6] ?? null,
        ];

        if ($lineup) {
            $lineup->update($data);
        } else {
            MatchPlayer::create($data);
        }

        $this->sendLineupToN8n($matchId);

        if(auth()->user()->can('club.create')){
            return redirect()->route('admin.competition.details', ['id' => $competitionObj->id])->with('success', '✅ Lineup saved successfully.');
        }   
        return redirect()->route('admin.dashboard')->with('success', '✅ Lineup saved successfully.');
    }


    private function sendLineupToN8n($matchId)
    {
        $lineup = MatchPlayer::where('match_id', $matchId)->first();
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
                'home_club.name as home_club_name',
                'home_club.avatar as home_club_avatar',
                'away_club.name as away_club_name',
                'away_club.avatar as away_club_avatar',
                'competition.name as competition_name',
                'competition.type as competition_type',
                'competition.id as competition_id'
            )
            ->where('match.id', $matchId)->first();

        if (!$lineup) {
            return response()->json(['error' => 'Lineup not found.'], 404);
        }

        $playerIds = [
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
        ];

        $players = Player::whereIn('id', $playerIds)->get();
        $payload = [
            'match_id' => $lineup->match_id,
            'club_id' => $lineup->club_id,
            'code' => $lineup->code,
            'text' => 'You have been selected to play in the Match: ' . $matchData->home_club_name . ' vs ' . $matchData->away_club_name . ' on ' . date('jS F Y', ($matchData->date)) . '. Show this QrCode when entering the field',
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

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://n8n.fieldpass.com.my/webhook/confirm_lineup', $payload);

        return $response->json();
    }

    private function playerUpdate($player_id, $matchId)
    {
        $payload = [
            'player_id' => $player_id,
            'match_id' => $matchId,
            'competition_id' => DB::table('match')->where('id', $matchId)->value('competition_id'),
            'data' => ''
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://n8n.fieldpass.com.my/webhook/player_update', $payload);

        return $response->json();
    }
}
