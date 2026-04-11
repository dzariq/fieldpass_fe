<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\match;

use App\Http\Controllers\Controller;
use App\Http\Requests\MatchRequest;
use App\Models\Admin;
use App\Models\Association;
use App\Models\Club;
use App\Models\Competition;  // <- Tambah line ni
use App\Models\Matches;
use App\Models\MatchPlayer;
use App\Models\MatchPlayerList;
use App\Models\MatchPosession;
use App\Models\Player;
use App\Services\MatchN8nLineupService;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MatchesController extends Controller
{
    public function index(HttpRequest $request): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.view']);

        $perPage = (int) min(max($request->get('per_page', 25), 10), 100);
        $baseQuery = Matches::query()
            ->with(['home_club', 'away_club', 'competition'])
            ->addSelect([
                'home_lineup_submitted' => MatchPlayer::query()
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('match_players.match_id', 'match.id')
                    ->whereColumn('match_players.club_id', 'match.home_club_id'),
                'away_lineup_submitted' => MatchPlayer::query()
                    ->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
                    ->whereColumn('match_players.match_id', 'match.id')
                    ->whereColumn('match_players.club_id', 'match.away_club_id'),
            ]);

        $admin_obj = Admin::find(auth()->user()->id);
        $associationIds = $admin_obj ? $admin_obj->associations()->pluck('association.id') : collect();
        // Qualify column: admin_club and club both have `id` — unqualified `id` causes SQL ambiguity (500).
        $clubIds = $admin_obj ? $admin_obj->clubs()->pluck('club.id')->map(fn ($v) => (int) $v)->values()->all() : [];
        $isSuperAdmin = auth()->user()->can('association.create');

        // Scope competitions:
        // - Super admin: no filter.
        // - Admin with assigned club(s): only competitions those clubs have joined (ACTIVE in competition_club).
        // - Association-only admin (no clubs): competitions under their association(s).
        $scopedCompetitionIds = null;
        if (! $isSuperAdmin) {
            if (count($clubIds) > 0) {
                $scopedCompetitionIds = DB::table('competition_club')
                    ->whereIn('club_id', $clubIds)
                    ->where('status', 'ACTIVE')
                    ->distinct()
                    ->pluck('competition_id');
            } elseif ($associationIds->count() > 0) {
                $scopedCompetitionIds = Competition::whereIn('association_id', $associationIds)->pluck('id');
            }
        }

        if ($scopedCompetitionIds !== null) {
            if ($scopedCompetitionIds->isEmpty()) {
                $baseQuery->whereRaw('0=1');
            } else {
                $baseQuery->whereIn('competition_id', $scopedCompetitionIds);
            }
        }

        if ($request->filled('matchweek')) {
            $baseQuery->where('matchweek', $request->matchweek);
        }
        if ($request->filled('competition_id')) {
            $cid = (int) $request->competition_id;
            if ($scopedCompetitionIds !== null && ! $scopedCompetitionIds->contains($cid)) {
                $baseQuery->whereRaw('0=1');
            } else {
                $baseQuery->where('competition_id', $cid);
            }
        }

        $matches = $baseQuery->orderBy('date', 'desc')->paginate($perPage)->withQueryString();

        $filterMatchweeks = Matches::query()
            ->when($scopedCompetitionIds !== null, function ($q) use ($scopedCompetitionIds) {
                if ($scopedCompetitionIds->isEmpty()) {
                    $q->whereRaw('0=1');
                } else {
                    $q->whereIn('competition_id', $scopedCompetitionIds);
                }
            })
            ->distinct()->orderBy('matchweek')->pluck('matchweek');

        $filterCompetitions = Competition::query()
            ->when($scopedCompetitionIds !== null, function ($q) use ($scopedCompetitionIds) {
                if ($scopedCompetitionIds->isEmpty()) {
                    $q->whereRaw('0=1');
                } else {
                    $q->whereIn('id', $scopedCompetitionIds);
                }
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

        $match = new Matches;
        $match->home_club_id = $request->home_club_id;
        $match->away_club_id = $request->away_club_id;
        $match->home_score = $request->home_score;
        $match->away_score = $request->away_score;
        $match->matchweek = $request->matchweek;

        // Combine date and time into Unix timestamp
        $dateTimeString = $request->date.' '.$request->time.':00'; // Add seconds
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
            'clubs' => $clubs,
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
        $dateTimeString = $request->date.' '.$request->time.':00'; // Add seconds
        $unixTimestamp = strtotime($dateTimeString);

        $match->date = $unixTimestamp;
        $match->competition_id = $request->competition_id;
        $match->save();

        Log::info('BOARD | looping clubs invited | '.json_encode($request->has('club_ids')));

        if ($request->has('club_ids') && ! empty($request->club_ids)) {
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
            if (! empty($results['attached'])) {
                $clubAdmins = [];

                Log::info('BOARD | club admins | '.json_encode($clubAdmins));

                $this->triggerEvent([
                    'action' => 'match_invitation',
                    'emails' => $clubAdmins,
                ]);
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

    public function details(int $match): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['match.details']);

        $matchModel = Matches::query()
            ->with(['home_club', 'away_club', 'competition', 'possessions.club', 'possessions.admin'])
            ->findOrFail($match);

        $summary = MatchPosession::summarizeForMatch($matchModel);

        return view('backend.pages.matches.details', [
            'match' => $matchModel,
            'summary' => $summary,
        ]);
    }

    public function printLineups(int $match): Renderable
    {
        $user = auth()->user();
        if (! $user->can('admin.view') && ! $user->can('match.edit')) {
            abort(403, 'Sorry !! You are unauthorized to perform this action.');
        }

        $matchModel = Matches::query()
            ->with(['home_club', 'away_club', 'competition'])
            ->findOrFail($match);

        $homeLineup = MatchPlayer::query()
            ->where('match_id', $matchModel->id)
            ->where('club_id', $matchModel->home_club_id)
            ->first();
        $awayLineup = MatchPlayer::query()
            ->where('match_id', $matchModel->id)
            ->where('club_id', $matchModel->away_club_id)
            ->first();

        return view('backend.pages.matches.lineups-print', [
            'match' => $matchModel,
            'homeHasLineup' => $homeLineup !== null,
            'awayHasLineup' => $awayLineup !== null,
            'homeRoster' => $this->rosterForPrint($homeLineup),
            'awayRoster' => $this->rosterForPrint($awayLineup),
            'kickoff' => Carbon::createFromTimestamp((int) $matchModel->date)->timezone('Asia/Kuala_Lumpur'),
        ]);
    }

    /**
     * @return array{starters: list<array{label: string, name: string, position: string}>, subs: list<array{label: string, name: string, position: string}>}
     */
    private function rosterForPrint(?MatchPlayer $row): array
    {
        if ($row === null) {
            return [
                'starters' => [],
                'subs' => [],
            ];
        }

        $idList = array_values(array_filter([
            $row->gk,
            $row->player1,
            $row->player2,
            $row->player3,
            $row->player4,
            $row->player5,
            $row->player6,
            $row->player7,
            $row->player8,
            $row->player9,
            $row->player10,
            $row->sub1,
            $row->sub2,
            $row->sub3,
            $row->sub4,
            $row->sub5,
            $row->sub6,
            $row->sub7,
            $row->sub8,
            $row->sub9,
        ], fn ($v) => $v !== null && $v !== ''));

        $players = $idList === [] ? collect() : Player::query()->whereIn('id', $idList)->get()->keyBy('id');

        $starterMap = [
            'gk' => 'GK',
            'player1' => '2',
            'player2' => '3',
            'player3' => '4',
            'player4' => '5',
            'player5' => '6',
            'player6' => '7',
            'player7' => '8',
            'player8' => '9',
            'player9' => '10',
            'player10' => '11',
        ];

        $starters = [];
        foreach ($starterMap as $field => $label) {
            $pid = $row->{$field};
            $p = $pid ? $players->get((int) $pid) : null;
            $starters[] = [
                'label' => $label,
                'name' => $p->name ?? '—',
                'position' => $p->position ?? '',
            ];
        }

        $subs = [];
        for ($i = 1; $i <= 9; $i++) {
            $field = 'sub'.$i;
            $pid = $row->{$field};
            $p = $pid ? $players->get((int) $pid) : null;
            $subs[] = [
                'label' => 'S'.$i,
                'name' => $p->name ?? '—',
                'position' => $p->position ?? '',
            ];
        }

        return [
            'starters' => $starters,
            'subs' => $subs,
        ];
    }

    public function recordMatchStart(HttpRequest $request, int $match): RedirectResponse|JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchModel = Matches::query()->findOrFail($match);
        if ($matchModel->started_at !== null) {
            return $this->possessionFail($request, __('Match has already been started.'));
        }

        $matchModel->started_at = now();
        if ($matchModel->status === 'NOT_STARTED') {
            $matchModel->status = 'ONGOING';
        }
        $matchModel->timer_pause_started_at = null;
        $matchModel->timer_paused_seconds = 0;
        $matchModel->save();

        MatchN8nLineupService::syncAllSubmittedLineupsForMatch((int) $matchModel->id);
        MatchN8nLineupService::notifyPlayerUpdateForMatch((int) $matchModel->id, false);

        return $this->possessionOk($request, $matchModel, __('Match start time recorded.'));
    }

    public function recordPossession(HttpRequest $request, int $match): RedirectResponse|JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchModel = Matches::query()->findOrFail($match);

        if ($matchModel->started_at === null) {
            return $this->possessionFail($request, __('Record match start before logging possession.'));
        }

        if ($matchModel->timer_pause_started_at !== null) {
            return $this->possessionFail($request, __('Resume the match timer before recording possession (clock paused — ball out of play).'));
        }

        $data = $request->validate([
            'club_id' => ['required', 'integer'],
        ]);

        $clubId = (int) $data['club_id'];
        $allowed = [(int) $matchModel->home_club_id, (int) $matchModel->away_club_id];
        if (! in_array($clubId, $allowed, true)) {
            return $this->possessionFail($request, __('Choose home or away team only.'));
        }

        $last = MatchPosession::query()
            ->where('match_id', $matchModel->id)
            ->orderByDesc('event_at')
            ->first();

        if ($last && (int) $last->club_id === $clubId) {
            return $this->possessionOk($request, $matchModel, __('Possession is already recorded for that team.'));
        }

        $matchModel->refresh();
        $playingSnap = (int) ($matchModel->playingElapsedSeconds() ?? 0);

        MatchPosession::query()->create([
            'match_id' => $matchModel->id,
            'club_id' => $clubId,
            'event_at' => now(),
            'playing_elapsed_seconds' => $playingSnap,
            'admin_id' => (int) auth()->id(),
        ]);

        return $this->possessionOk($request, $matchModel, __('Possession recorded.'));
    }

    public function pauseMatchTimer(HttpRequest $request, int $match): RedirectResponse|JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchModel = Matches::query()->findOrFail($match);
        if (! $matchModel->started_at) {
            return $this->possessionFail($request, __('Match has not started yet.'));
        }
        if ($matchModel->timer_pause_started_at) {
            return $this->possessionFail($request, __('Timer is already paused.'));
        }

        $matchModel->timer_pause_started_at = now();
        $matchModel->save();

        return $this->possessionOk($request, $matchModel, __('Timer paused.'));
    }

    public function resumeMatchTimer(HttpRequest $request, int $match): RedirectResponse|JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchModel = Matches::query()->findOrFail($match);
        if (! $matchModel->timer_pause_started_at) {
            return $this->possessionFail($request, __('Timer is not paused.'));
        }

        $matchModel->timer_paused_seconds = (int) $matchModel->timer_paused_seconds
            + (int) $matchModel->timer_pause_started_at->diffInSeconds(now());
        $matchModel->timer_pause_started_at = null;
        $matchModel->save();

        return $this->possessionOk($request, $matchModel, __('Timer resumed.'));
    }

    public function resetMatchPossession(HttpRequest $request, int $match): RedirectResponse|JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.edit']);

        $matchModel = Matches::query()->findOrFail($match);

        DB::transaction(function () use ($matchModel): void {
            MatchPosession::query()->where('match_id', $matchModel->id)->delete();
            $matchModel->started_at = null;
            $matchModel->timer_pause_started_at = null;
            $matchModel->timer_paused_seconds = 0;
            if ($matchModel->status === 'ONGOING') {
                $matchModel->status = 'NOT_STARTED';
            }
            $matchModel->save();
        });

        $matchModel->refresh();

        MatchN8nLineupService::notifyPlayerUpdateForMatch((int) $matchModel->id, false);

        return $this->possessionOk($request, $matchModel, __('Timer and possession log cleared.'));
    }

    private function possessionWantsJson(HttpRequest $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }

    private function possessionJsonResponse(Matches $matchModel, ?string $message = null, bool $success = true): JsonResponse
    {
        $matchModel->refresh();
        $matchModel->load(['possessions.club', 'possessions.admin']);

        return response()->json([
            'success' => $success,
            'message' => $message,
            'playing_seconds' => $matchModel->playingElapsedSeconds(),
            'started_at' => $matchModel->started_at?->toIso8601String(),
            'timer_pause_started_at' => $matchModel->timer_pause_started_at?->toIso8601String(),
            'timer_paused_seconds' => (int) ($matchModel->timer_paused_seconds ?? 0),
            'is_paused' => $matchModel->timer_pause_started_at !== null,
            'match_status' => $matchModel->status,
            'summary' => MatchPosession::summarizeForMatch($matchModel),
            'possessions' => $matchModel->possessions->map(function ($row) {
                return [
                    'event_at' => $row->event_at?->format('Y-m-d H:i:s'),
                    'club_name' => $row->club?->name ?? '—',
                    'admin_name' => $row->admin?->name ?? '—',
                ];
            })->values()->all(),
        ], $success ? 200 : 422);
    }

    private function possessionFail(HttpRequest $request, string $message): JsonResponse|RedirectResponse
    {
        if ($this->possessionWantsJson($request)) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return back()->with('error', $message);
    }

    private function possessionOk(HttpRequest $request, Matches $matchModel, ?string $message = null): JsonResponse|RedirectResponse
    {
        if ($this->possessionWantsJson($request)) {
            return $this->possessionJsonResponse($matchModel, $message, true);
        }

        return $message ? back()->with('success', $message) : back();
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
            if (! $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token is required.',
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
                        'message' => 'Invalid token format.',
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
                        'message' => 'Invalid QR code format',
                    ]);
                }

                $playerId = $tokenData[0];
                $matchId = $tokenData[1];
            }

            // Validate player exists
            $player = Player::find($playerId);
            if (! $player) {
                return response()->json([
                    'success' => false,
                    'message' => 'Player not found',
                ]);
            }

            // Validate match exists
            $match = Matches::find($matchId);
            if (! $match) {
                return response()->json([
                    'success' => false,
                    'message' => 'Match not found',
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
                    'message' => 'Player already checked in at '.$existingCheckin->checkin_at->format('H:i A'),
                ]);
            }

            // Create checkin record
            $checkin = MatchPlayerList::create([
                'player_id' => $playerId,
                'club_id' => $clubId,
                'match_id' => $matchId,
                'checkin_at' => Carbon::now(),
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
                    'checkin_at' => $checkin->checkin_at->toDateTimeString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Checkin verification error: '.$e->getMessage(), [
                'token' => $request->input('token'),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during checkin verification: '.$e->getMessage(),
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

            if (! $matchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active match found',
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
                    'checkin_at' => $checkin->checkin_at->toDateTimeString(),
                ];
            });

            return response()->json([
                'success' => true,
                'players' => $players,
                'match_id' => $matchId,
                'total_count' => $players->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Checkin list error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load players list'.$e->getMessage(),
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
                    'sub7' => 'Sub7',
                    'sub8' => 'Sub8',
                    'sub9' => 'Sub9',
                ];

                foreach ($positions as $column => $position) {
                    if (! empty($mp->$column)) {
                        $players->push((object) [
                            'player_id' => $mp->$column,
                            'position' => $position,
                            'club_name' => $mp->club_name,
                            'registered_at' => $mp->created_at,
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
                    'registered_at' => $item->registered_at,
                ];
            })->sortBy(['club_name', 'position'])->values(); // Add values() to reset array keys

            // TAMBAH NI - Return JSON response for success case
            return response()->json([
                'success' => true,
                'players' => $finalPlayers,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load registered players: '.$e->getMessage(),
            ], 500);
        }
    }

    private function getPlayerClubName($player, $clubId)
    {
        return Club::find($clubId)->name;
    }
}
