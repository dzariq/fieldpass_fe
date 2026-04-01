<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\match;

use App\Models\Competition;
use App\Models\MatchPlayerList;
use App\Models\Player;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;  // <- Tambah line ni
use App\Http\Controllers\Controller;
use App\Http\Requests\MatchRequest;
use App\Models\Association;
use App\Models\Club;
use App\Models\Matches;
use App\Models\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MatchesController extends Controller
{
    public function index(HttpRequest $request): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.view']);

        $perPage = (int) min(max($request->get('per_page', 25), 10), 100);
        $baseQuery = Matches::query()->with(['home_club', 'away_club', 'competition']);

        if (!auth()->user()->can('competition.create')) {
            $admin_obj = Admin::find(auth()->user()->id);
            $associationIds = $admin_obj->associations()->pluck('id');
            $competitionIds = Competition::whereIn('association_id', $associationIds)->pluck('id');
            $baseQuery->whereIn('competition_id', $competitionIds);
        }

        if ($request->filled('matchweek')) {
            $baseQuery->where('matchweek', $request->matchweek);
        }
        if ($request->filled('competition_id')) {
            $baseQuery->where('competition_id', $request->competition_id);
        }

        $matches = $baseQuery->orderBy('date', 'desc')->paginate($perPage)->withQueryString();

        $filterMatchweeks = Matches::query()
            ->when(!auth()->user()->can('competition.create'), function ($q) {
                $admin_obj = Admin::find(auth()->user()->id);
                $associationIds = $admin_obj->associations()->pluck('id');
                $competitionIds = Competition::whereIn('association_id', $associationIds)->pluck('id');
                $q->whereIn('competition_id', $competitionIds);
            })
            ->distinct()->orderBy('matchweek')->pluck('matchweek');

        $filterCompetitions = Competition::query()
            ->when(!auth()->user()->can('competition.create'), function ($q) {
                $admin_obj = Admin::find(auth()->user()->id);
                $associationIds = $admin_obj->associations()->pluck('id');
                $q->whereIn('association_id', $associationIds);
            })
            ->orderBy('name')->get(['id', 'name']);

        return view('backend.pages.matches.index', [
            'matches' => $matches,
            'filterMatchweeks' => $filterMatchweeks,
            'filterCompetitions' => $filterCompetitions,
        ]);
    }

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.create']);

        if (auth()->user()->can('competition.create')) {
            return view('backend.pages.matches.create', [
                'competitions' => Competition::orderBy('name')->get(['id', 'name']),
            ]);
        } else {
            $admin_obj = Admin::find(auth()->user()->id);
            $associationIds = $admin_obj->associations()->pluck('id');
            $competitionIds = Competition::whereIn('association_id', $associationIds)->pluck('id');

            return view('backend.pages.matches.create', [
                'competitions' => Competition::whereIn('id', $competitionIds)->orderBy('name')->get(['id', 'name']),
            ]);
        }
    }

    public function store(MatchRequest $request): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.create']);

        $match = new Matches();
        $match->home_club_id = $request->home_club_id;
        $match->away_club_id = $request->away_club_id;
        $match->home_score = $request->home_score;
        $match->away_score = $request->away_score;
        $match->matchweek = $request->matchweek;

                // Combine date and time into Unix timestamp
        $dateTimeString = $request->date . ' ' . $request->time . ':00'; // Add seconds
        $unixTimestamp = strtotime($dateTimeString);

        $match->date = $unixTimestamp;
        $match->competition_id = $request->competition_id;
        $match->save();

        session()->flash('success', __('match has been created.'));
        return redirect()->route('admin.matches.index');
    }

    public function edit(int $id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);
        $match = Matches::findOrFail($id);

        $clubs = Club::where('association_id', $match->association_id)->get();

        return view('backend.pages.matches.edit', [
            'match' => $match,
            'associations' => Association::all(),
            'clubs' => $clubs
        ]);
    }

    public function update(MatchRequest $request, int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $match = Matches::findOrFail($id);
        $match->home_club_id = $request->home_club_id;
        $match->away_club_id = $request->away_club_id;
        $match->home_score = $request->home_score;
        $match->away_score = $request->away_score;
        $match->matchweek = $request->matchweek;

        // Combine date and time into Unix timestamp
        $dateTimeString = $request->date . ' ' . $request->time . ':00'; // Add seconds
        $unixTimestamp = strtotime($dateTimeString);

        $match->date = $unixTimestamp;
        $match->competition_id = $request->competition_id;
        $match->save();

        Log::info("BOARD | looping clubs invited | " . json_encode($request->has('club_ids')));

        if ($request->has('club_ids') && !empty($request->club_ids)) {
            // Step 1: Fetch IDs with 'ACTIVE' status
            $activeClubIds = $match->clubs()
                ->wherePivotIn('club_id', $request->club_ids)
                ->wherePivot('status', 'ACTIVE')
                ->pluck('club_id')
                ->toArray();

            // Step 2: Exclude 'ACTIVE' IDs from the sync list
            $filteredClubIds = array_diff($request->club_ids, $activeClubIds);

            // Step 3: Prepare the remaining IDs with 'INVITED' status
            $idsWithStatus = array_fill_keys($filteredClubIds, ['status' => 'INVITED']);

            // Step 4: Sync without detaching
            $results = $match->clubs()->sync($idsWithStatus, false);
            if (!empty($results['attached'])) {
                $clubAdmins = array();

                Log::info("BOARD | club admins | " . json_encode($clubAdmins));

                $this->triggerEvent(array(
                    'action' => 'match_invitation',
                    'emails' => $clubAdmins,
                ));
            }
        }

        session()->flash('success', 'match has been updated.');
        return back();
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.delete']);

        $match = Matches::findOrFail($id);
        $match->delete();
        session()->flash('success', 'match has been deleted.');
        return back();
    }

    public function details($id)
    {
        $this->checkAuthorization(auth()->user(), ['match.details']);

        return view('backend.pages.matches.details', [
            'match' => Matches::find($id),
        ]);
    }

    public function checkin()
    {
        return view('backend.pages.matches.checkin', []);
    }

    public function checkinVerify(HttpRequest $request)
    {
        try {
            $token = $request->input('token');

            // Validate token presence
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is required.'
                ]);
            }

            // Decode/decrypt the token to get player and match information
            // Assuming token format: "player_id:match_id:timestamp" or "code_player_id"
            if (strpos($token, '_') !== false) {
                // Handle old format: code_player_id
                $tokenParts = explode('_', $token);
                if (count($tokenParts) !== 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid token format.'
                    ]);
                }

                $code = trim($tokenParts[0]);
                $playerId = trim($tokenParts[1]);

                // Get match from code (you'll need to implement this based on your logic)
                $matchId = $this->getMatchIdFromCode($code);
            } else {
                // Handle new format: base64 encoded "player_id:match_id:timestamp"
                $tokenData = explode(':', base64_decode($token));

                if (count($tokenData) < 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid QR code format'
                    ]);
                }

                $playerId = $tokenData[0];
                $matchId = $tokenData[1];
            }

            // Validate player exists
            $player = Player::find($playerId);
            if (!$player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found'
                ]);
            }

            // Validate match exists
            $match = Matches::find($matchId);
            if (!$match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found'
                ]);
            }

            // Get club ID from player's clubs (assuming many-to-many relationship)
            $clubId = $player->clubs()->first()->id ?? Auth::guard('admin')->user()->club_id ?? 1;

            // Check if player already checked in for this match
            $existingCheckin = MatchPlayerList::where('player_id', $playerId)
                ->where('match_id', $matchId)
                ->first();

            if ($existingCheckin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player already checked in at ' . $existingCheckin->checkin_at->format('H:i A')
                ]);
            }

            // Create checkin record
            $checkin = MatchPlayerList::create([
                'player_id' => $playerId,
                'club_id' => $clubId,
                'match_id' => $matchId,
                'checkin_at' => Carbon::now()
            ]);

            // Return success with player data for UI update
            return response()->json([
                'success' => true,
                'message' => "Player {$player->name} checked in successfully!",
                'player' => [
                    'id' => $player->id,
                    'name' => $player->name,
                    'identity_number' => $player->identity_number,
                    'club_name' => $this->getPlayerClubName($player, $clubId),
                    'position' => $this->getPlayerPosition($playerId, $matchId),
                    'checkin_at' => $checkin->checkin_at->toDateTimeString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Checkin verification error: ' . $e->getMessage(), [
                'token' => $request->input('token'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during checkin verification: ' . $e->getMessage()
            ]);
        }
    }

    public function checkin_list(HttpRequest $request)
    {
        try {
            // Get current match ID from session, request, or current active match
            $matchId = session('current_match_id') ??
                $request->input('match_id') ??
                $this->getCurrentActiveMatchId();

            if (!$matchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active match found'
                ]);
            }

            // Get club ID
            $clubId = Auth::guard('admin')->user()->club_id ?? 1;

            // Get all checked-in players for this match
            $checkedInPlayers = MatchPlayerList::where('match_id', $matchId)
                ->where('club_id', $clubId)
                ->whereNotNull('checkin_at')
                ->with('player:id,name,identity_number,position')
                ->orderBy('checkin_at', 'desc')
                ->get();

            $players = $checkedInPlayers->map(function ($checkin) use ($clubId) {
                return [
                    'id' => $checkin->player->id,
                    'name' => $checkin->player->name,
                    'club_name' => $this->getPlayerClubName($checkin->player, $clubId),
                    'identity_number' => $checkin->player->identity_number,
                    'checkin_at' => $checkin->checkin_at->toDateTimeString()
                ];
            });

            return response()->json([
                'success' => true,
                'players' => $players,
                'match_id' => $matchId,
                'total_count' => $players->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Checkin list error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load players list' . $e->getMessage()
            ]);
        }
    }

    /**
     * Helper method to get match ID from code (implement based on your logic)
     */
    private function getMatchIdFromCode($code)
    {
        // You'll need to implement this based on how you generate match codes
        // This is just an example - adjust based on your actual implementation

        $matchPlayer = \App\Models\MatchPlayer::where('code', $code)->first();
        return $matchPlayer ? $matchPlayer->match_id : null;
    }

    /**
     * Helper method to get current active match ID
     */
    private function getCurrentActiveMatchId()
    {
        // Get today's matches or the most recent upcoming match
        // $today = Carbon::today()->timestamp;

        // $match = Matches::where('date', '>=', $today)
        //     ->orderBy('date', 'asc')
        //     ->first();

        // return $match ? $match->id : null;
        return 1;
    }

    public function getRegisteredPlayers(Request $request)
    {
        try {
            // Assuming you have a current match ID or get it from session/request
            // $matchId = $request->get('match_id'); // or get from session
            $matchId = 1; // or get from session

            // Get all match_players records for this match
            $matchPlayers = DB::table('match_players as mp')
                ->join('club as c', 'mp.club_id', '=', 'c.id')
                ->where('mp.match_id', $matchId)
                ->select('mp.*', 'c.name as club_name')
                ->get();

            $players = collect();

            foreach ($matchPlayers as $mp) {
                // Define position mapping
                $positions = [
                    'gk' => 'GK',
                    'player1' => 'Player1',
                    'player2' => 'Player2',
                    'player3' => 'Player3',
                    'player4' => 'Player4',
                    'player5' => 'Player5',
                    'player6' => 'Player6',
                    'player7' => 'Player7',
                    'player8' => 'Player8',
                    'player9' => 'Player9',
                    'player10' => 'Player10',
                    'sub1' => 'Sub1',
                    'sub2' => 'Sub2',
                    'sub3' => 'Sub3',
                    'sub4' => 'Sub4',
                    'sub5' => 'Sub5',
                    'sub6' => 'Sub6',
                    'sub7' => 'Sub7'
                ];

                foreach ($positions as $column => $position) {
                    if (!empty($mp->$column)) {
                        $players->push((object)[
                            'player_id' => $mp->$column,
                            'position' => $position,
                            'club_name' => $mp->club_name,
                            'registered_at' => $mp->created_at
                        ]);
                    }
                }
            }

            // Get player details
            $playerIds = $players->pluck('player_id')->toArray();
            $playerDetails = DB::table('players')
                ->whereIn('id', $playerIds)
                ->get()
                ->keyBy('id');

            // Combine data
            $finalPlayers = $players->map(function ($item) use ($playerDetails) {
                $player = $playerDetails[$item->player_id] ?? null;

                return [
                    'id' => $item->player_id,
                    'name' => $player->name ?? 'Unknown',
                    'identity_number' => $player->identity_number ?? null,
                    'position' => $item->position,
                    'club_name' => $item->club_name,
                    'registered_at' => $item->registered_at
                ];
            })->sortBy(['club_name', 'position'])->values(); // Add values() to reset array keys

            // TAMBAH NI - Return JSON response for success case
            return response()->json([
                'success' => true,
                'players' => $finalPlayers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load registered players: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getPlayerClubName($player, $clubId)
    {
        return Club::find($clubId)->name;
    }
}
