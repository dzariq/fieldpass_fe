<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Models\CompetitionClub;
use App\Notifications\NewCompetitionInviteNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionRequest;
use App\Models\Association;
use App\Models\Club;
use App\Models\Competition;
use App\Models\Admin;
use App\Models\FantasyPoints;
use App\Models\FantasyRules;
use App\Models\FantasyTimeline;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use DB;
use Illuminate\Support\Facades\Log;


class FantasyController extends Controller
{
    public function index(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.view']);

        $admin_obj = Admin::find(auth()->user()->id);
        $associationIds = $admin_obj->associations->pluck('id')->toArray();

        if (count($associationIds) == 0) {
            return view('backend.pages.fantasy.index', [
                'fantasy' => FantasyRules::all(),
            ]);
        } else {
            // Get competitions that belong to the admin's associations
            $competitions = Competition::whereIn('association_id', $associationIds)->get();

            // Then get fantasy rules for those competitions
            $fantasy = FantasyRules::whereIn('competition_id', $competitions->pluck('id'))->get();

            return view('backend.pages.fantasy.index', [
                'fantasy' => $fantasy,
            ]);
        }
    }

public function points(): Renderable
{
    $this->checkAuthorization(auth()->user(), ['competition.view']);

    $admin = Admin::with('associations')->find(auth()->user()->id);
    $associationIds = $admin->associations->pluck('id')->toArray();

    if (empty($associationIds)) {
        // Get all competitions
        $allCompetitions = DB::table('competition')->get();
    } else {
        // Get only competitions in admin's associations
        $allCompetitions = DB::table('competition')
            ->whereIn('association_id', $associationIds)
            ->get();
    }

    // Get competitions that have fantasy points (grouped)
    $competitionsWithPoints = DB::table('fantasy_points')
        ->join('competition', 'fantasy_points.competition_id', '=', 'competition.id')
        ->select(
            'competition.id',
            'competition.name',
            'competition.association_id',
            DB::raw('COUNT(fantasy_points.id) as positions_count'),
            DB::raw('MAX(fantasy_points.updated_at) as last_updated')
        )
        ->when(!empty($associationIds), function($query) use ($associationIds) {
            return $query->whereIn('competition.association_id', $associationIds);
        })
        ->groupBy('competition.id', 'competition.name', 'competition.association_id')
        ->get();

    return view('backend.pages.fantasy.points', [
        'competitionsWithPoints' => $competitionsWithPoints,
    ]);
}

public function points_new(): Renderable
{
    $this->checkAuthorization(auth()->user(), ['competition.create']);

    $admin = Admin::with('associations')->find(auth()->user()->id);
    $associationIds = $admin->associations->pluck('id')->toArray();

    if (empty($associationIds)) {
        $competitions = Competition::all();
    } else {
        $competitions = Competition::whereIn('association_id', $associationIds)->get();
    }

    // Get existing fantasy points for reference
    $existingPoints = DB::table('fantasy_points')
        ->join('competition', 'fantasy_points.competition_id', '=', 'competition.id')
        ->select('fantasy_points.*', 'competition.name as competition_name')
        ->get()
        ->groupBy('competition_id');

    // Common event types with suggested points
    $eventTypes = [
        'goal' => ['name' => 'Goal', 'icon' => '⚽', 'suggested' => 5],
        'assist' => ['name' => 'Assist', 'icon' => '🎯', 'suggested' => 3],
        'clean_sheet' => ['name' => 'Clean Sheet', 'icon' => '🛡️', 'suggested' => 4],
        'penalty_saved' => ['name' => 'Penalty Saved', 'icon' => '🧤', 'suggested' => 5],
        'penalty_missed' => ['name' => 'Penalty Missed', 'icon' => '❌', 'suggested' => -2],
        'yellow_card' => ['name' => 'Yellow Card', 'icon' => '🟨', 'suggested' => -1],
        'red_card' => ['name' => 'Red Card', 'icon' => '🟥', 'suggested' => -3],
        'own_goal' => ['name' => 'Own Goal', 'icon' => '⚠️', 'suggested' => -2],
        'minutes_played' => ['name' => 'Minutes Played (60+)', 'icon' => '⏱️', 'suggested' => 2],
        'win_bonus' => ['name' => 'Win Bonus', 'icon' => '🏆', 'suggested' => 4],
        'played_less_60' => ['name' => 'Played Less Than 60 Min', 'icon' => '⏰', 'suggested' => 1],
    ];

    return view('backend.pages.fantasy.points_new', compact('competitions', 'existingPoints', 'eventTypes'));
}

public function points_store(Request $request): RedirectResponse
{
    $this->checkAuthorization(auth()->user(), ['competition.create']);

    $request->validate([
        'competition_id' => 'required|exists:competition,id',
        'positions' => 'required|array|size:4',
        'positions.*.position' => 'required|in:GK,DF,MF,ST',
        'positions.*.goal' => 'required|integer',
        'positions.*.assist' => 'required|integer',
        'positions.*.clean_sheet' => 'required|integer',
        'positions.*.penalty_saved' => 'required|integer',
        'positions.*.minutes_played_60' => 'required|integer',
        'positions.*.win_bonus' => 'required|integer',
        'positions.*.yellow_card' => 'required|integer',
        'positions.*.red_card' => 'required|integer',
        'positions.*.own_goal' => 'required|integer',
        'positions.*.penalty_missed' => 'required|integer',
        'positions.*.concede_goal' => 'required|integer',
        'positions.*.played_less_60' => 'required|integer',
    ]);

    try {
        DB::beginTransaction();

        // Check if points already exist for this competition
        $exists = DB::table('fantasy_points')
            ->where('competition_id', $request->competition_id)
            ->exists();

        if ($exists) {
            DB::rollBack();
            return back()->withErrors([
                'error' => '❌ Points rules already exist for this competition. Please edit instead.'
            ])->withInput();
        }

        // Insert all 4 positions
        foreach ($request->positions as $positionData) {
            DB::table('fantasy_points')->insert([
                'competition_id' => $request->competition_id,
                'position' => $positionData['position'],
                'score' => $positionData['goal'],
                'assist' => $positionData['assist'],
                'clean_sheet' => $positionData['clean_sheet'],
                'pen_saved' => $positionData['penalty_saved'],
                'played_60' => $positionData['minutes_played_60'],
                'win_bonus' => $positionData['win_bonus'],
                'yellow' => $positionData['yellow_card'],
                'red' => $positionData['red_card'],
                'owngoal' => $positionData['own_goal'],
                'pen_missed' => $positionData['penalty_missed'],
                'concede' => $positionData['concede_goal'],
                'played_less_60' => $positionData['played_less_60'],
                'created_at' => now()->timestamp,
                'updated_at' => now()->timestamp,
            ]);
        }

        DB::commit();

        return redirect()->route('admin.fantasy.points')
            ->with('success', '✅ Fantasy points rules created successfully for all positions!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to create fantasy points: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to create fantasy points rules.'.$e->getMessage()])
            ->withInput();
    }
}
public function points_update(Request $request, $competitionId): RedirectResponse
{
    $this->checkAuthorization(auth()->user(), ['competition.edit']);

    $request->validate([
        'positions' => 'required|array|size:4',
        'positions.*.position' => 'required|in:GK,DF,MF,ST',
        'positions.*.score' => 'required|integer',
        'positions.*.assist' => 'required|integer',
        'positions.*.clean_sheet' => 'required|integer',
        'positions.*.pen_saved' => 'required|integer',
        'positions.*.played_60' => 'required|integer',
        'positions.*.win_bonus' => 'required|integer',
        'positions.*.yellow' => 'required|integer',
        'positions.*.red' => 'required|integer',
        'positions.*.owngoal' => 'required|integer',
        'positions.*.pen_missed' => 'required|integer',
        'positions.*.concede' => 'required|integer',
        'positions.*.played_less_60' => 'required|integer',
    ]);

    try {
        DB::beginTransaction();

        // Update all 4 positions
        foreach ($request->positions as $positionData) {
            DB::table('fantasy_points')
                ->where('competition_id', $competitionId)
                ->where('position', $positionData['position'])
                ->update([
                    'score' => $positionData['score'],
                    'assist' => $positionData['assist'],
                    'clean_sheet' => $positionData['clean_sheet'],
                    'pen_saved' => $positionData['pen_saved'],
                    'played_60' => $positionData['played_60'],
                    'win_bonus' => $positionData['win_bonus'],
                    'yellow' => $positionData['yellow'],
                    'red' => $positionData['red'],
                    'owngoal' => $positionData['owngoal'],
                    'pen_missed' => $positionData['pen_missed'],
                    'concede' => $positionData['concede'],
                    'played_less_60' => $positionData['played_less_60'],
                    'updated_at' => now()->timestamp,
                ]);
        }

        DB::commit();

        return redirect()->route('admin.fantasy.points')
            ->with('success', '✅ Fantasy points rules updated successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to update fantasy points: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to update fantasy points rules.'.$e->getMessage()])
            ->withInput();
    }
}

public function points_edit($competitionId): Renderable
{
    $this->checkAuthorization(auth()->user(), ['competition.edit']);

    // ===============================
    // Get admin & allowed associations
    // ===============================
    $admin = Admin::with('associations')->findOrFail(auth()->user()->id);
    $associationIds = $admin->associations->pluck('id')->toArray();

    // ===============================
    // Validate competition ownership
    // ===============================
    if (!empty($associationIds)) {
        $competition = DB::table('competition')
            ->where('id', $competitionId)
            ->whereIn('association_id', $associationIds)
            ->first();
    } else {
        // Super admin - can access all
        $competition = DB::table('competition')
            ->where('id', $competitionId)
            ->first();
    }

    if (!$competition) {
        abort(403, 'You are not allowed to edit this competition');
    }

    // ===============================
    // Get fantasy points by position
    // ===============================
    $points = DB::table('fantasy_points')
        ->where('competition_id', $competitionId)
        ->get();

    if ($points->isEmpty()) {
        return redirect()->route('admin.fantasy.points')
            ->withErrors(['error' => '❌ No fantasy points rules found for this competition. Please create one first.']);
    }

    /**
     * Convert to:
     * [
     *   'GK' => {...},
     *   'DF' => {...},
     *   'MF' => {...},
     *   'ST' => {...}
     * ]
     */
    $positions = $points->keyBy('position');

    // Check if all 4 positions exist
    $requiredPositions = ['GK', 'DF', 'MF', 'ST'];
    foreach ($requiredPositions as $pos) {
        if (!isset($positions[$pos])) {
            return redirect()->route('admin.fantasy.points')
                ->withErrors(['error' => "❌ Missing fantasy points for position: {$pos}. Please recreate the rules."]);
        }
    }

    // ===============================
    // Event Types (Optional / Dynamic UI)
    // ===============================
    $eventTypes = [
        'goal'             => ['name' => 'Goal', 'icon' => '⚽'],
        'assist'           => ['name' => 'Assist', 'icon' => '🎯'],
        'clean_sheet'      => ['name' => 'Clean Sheet', 'icon' => '🛡️'],
        'penalty_saved'    => ['name' => 'Penalty Saved', 'icon' => '🧤'],
        'penalty_missed'   => ['name' => 'Penalty Missed', 'icon' => '❌'],
        'yellow_card'      => ['name' => 'Yellow Card', 'icon' => '🟨'],
        'red_card'         => ['name' => 'Red Card', 'icon' => '🟥'],
        'own_goal'         => ['name' => 'Own Goal', 'icon' => '⚠️'],
        'minutes_played_60'=> ['name' => 'Played 60+ Min', 'icon' => '⏱️'],
        'played_less_60'   => ['name' => 'Played < 60 Min', 'icon' => '⏰'],
        'win_bonus'        => ['name' => 'Win Bonus', 'icon' => '🏆'],
        'concede_goal'     => ['name' => 'Concede Goal', 'icon' => '⚽'],
    ];

    return view('backend.pages.fantasy.points_edit', compact('competition', 'positions', 'eventTypes'));
}
public function points_delete($id): RedirectResponse
{
    $this->checkAuthorization(auth()->user(), ['competition.delete']);

    try {
        DB::table('fantasy_points')->where('id', $id)->delete();

        return redirect()->route('admin.fantasy.points')
            ->with('success', '✅ Fantasy points rule deleted successfully!');

    } catch (\Exception $e) {
        \Log::error('Failed to delete fantasy points: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to delete fantasy points rule.']);
    }
}

    public function create(): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.create']);

        $admin_obj = Admin::find(auth()->user()->id);
        $associationIds = $admin_obj->associations->pluck('id')->toArray();

        if (count($associationIds) == 0) {
            $competitions = Competition::all();
        } else {
            $competitions = Competition::whereIn('association_id', $associationIds)->get();
        }

        return view('backend.pages.fantasy.create', compact('competitions'));
    }


    /**
 * Delete a matchweek
 */
public function deleteMatchweek($competitionId, $matchweek)
{
    try {
        DB::beginTransaction();

        // Check if matchweek exists
        $timeline = DB::table('fantasy_timeline')
            ->where('competition_id', $competitionId)
            ->where('matchweek', $matchweek)
            ->first();

        if (!$timeline) {
            return back()->withErrors(['error' => '❌ Matchweek not found.']);
        }

        // Check if matchweek is already DONE
        if (($timeline->status ?? 'ACTIVE') === 'DONE') {
            return back()->withErrors(['error' => '❌ Cannot delete a DONE matchweek. Please contact system administrator.']);
        }


        // Delete matchweek timeline
        DB::table('fantasy_timeline')
            ->where('competition_id', $competitionId)
            ->where('matchweek', $matchweek)
            ->delete();

        // Optional: Delete matchweek points/rankings
        DB::table('fantasy_player_match_events')
            ->where('competition_id', $competitionId)
            ->where('matchweek', $matchweek)
            ->delete();

        DB::commit();

        return back()->with('success', "✅ Matchweek {$matchweek} deleted successfully!");

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to delete matchweek: ' . $e->getMessage());
        return back()->withErrors(['error' => '❌ Failed to delete matchweek: ' . $e->getMessage()]);
    }
}
    public function store(Request $request)
    {
        $this->checkAuthorization(auth()->user(), ['competition.create']);

        $request->validate([
            'competition_id' => 'required|exists:competition,id',
            'total_matchweeks' => 'required|integer|min:1',
            'matchweeks.*.transfer' => 'required|integer|min:0',
            'matchweeks.*.max_same_club' => 'required|integer|min:0',
            'matchweeks.*.benchboost' => 'required|integer|min:0',
            'matchweeks.*.wildcard' => 'required|integer|min:0',
            'matchweeks.*.triple' => 'required|integer|min:0',
            'matchweeks.*.credit' => 'required|numeric|min:0',
        ]);

        // Check if competition already has fantasy timeline
        $existingTimeline = FantasyTimeline::where('competition_id', $request->competition_id)->first();

        if ($existingTimeline) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['competition_id' => 'Fantasy timeline already exists for this competition. Please edit the existing timeline or choose a different competition.']);
        }

        foreach ($request->matchweeks as $matchweek => $data) {
            if (empty($data['cutoff_time'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['competition_id' => 'Matchweek '.$matchweek.' cutoff time is required.']);
            }
        }

        // // Alternative: You can also check using FantasyRules
        // $existingRules = FantasyRules::where('competition_id', $request->competition_id)->first();

        // if ($existingRules) {
        //     return redirect()->back()
        //         ->withInput()
        //         ->withErrors(['competition_id' => 'Fantasy rules already exist for this competition. Please edit the existing rules or choose a different competition.']);
        // }

        // Create or update fantasy rules
        // FantasyRules::create([
        //     'competition_id' => $request->competition_id,
        //     'matchweeks' => $request->total_matchweeks,
        //     'matchweek' => 1,
        //     'season' => date('Y'),
        //     // Add other default values from first matchweek
        //     'transfer' => $request->matchweeks[1]['transfer'] ?? 1,
        //     'max_same_club' => $request->matchweeks[1]['max_same_club'] ?? 3,
        //     'credit' => $request->matchweeks[1]['credit'] ?? 100,
        //     'benchboost' => $request->matchweeks[1]['benchboost'] ?? 1,
        //     'wildcard' => $request->matchweeks[1]['wildcard'] ?? 1,
        //     'triple' => $request->matchweeks[1]['triple'] ?? 1,
        //     'GK' => 2,
        //     'DF' => 5,
        //     'MF' => 5,
        //     'ST' => 3,
        // ]);

        // Create fantasy timeline for each matchweek
        foreach ($request->matchweeks as $matchweek => $data) {
            FantasyTimeline::create([
                'competition_id' => $request->competition_id,
                'matchweek' => $matchweek,
                'transfer' => $data['transfer'],
                'max_same_club' => $data['max_same_club'],
                'benchboost' => $data['benchboost'],
                'wildcard' => $data['wildcard'],
                'triple' => $data['triple'],
                'credit' => $data['credit'],
                'cutoff_time' => !empty($data['cutoff_time']) ? strtotime($data['cutoff_time']) : null,
            ]);
        }

        session()->flash('success', 'Fantasy timeline created successfully!');
        return redirect()->route('admin.fantasy.index');
    }
    public function edit($competition_id): Renderable
    {
        $this->checkAuthorization(auth()->user(), ['competition.edit']);

        $competition = Competition::with('fantasyTimelines')->findOrFail($competition_id);
        $fantasyRules = FantasyRules::where('competition_id', $competition_id)->first();

        // Get all matchweeks for this competition
        $timelines = FantasyTimeline::where('competition_id', $competition_id)
            ->orderBy('matchweek', 'asc')
            ->get();

        return view('backend.pages.fantasy.edit', compact('competition', 'fantasyRules', 'timelines'));
    }

       public function update(Request $request, $competition_id)
    {
        try {
            // Update existing matchweeks
            if ($request->has('matchweeks')) {
                foreach ($request->matchweeks as $matchweek => $data) {
                    $timeline = FantasyTimeline::where('competition_id', $competition_id)
                        ->where('matchweek', $matchweek)
                        ->first();

                    if ($timeline && $timeline->status !== 'DONE') {
                        $timeline->update([
                            'transfer' => $data['transfer'],
                            'max_same_club' => $data['max_same_club'],
                            'credit' => $data['credit'],
                            'benchboost' => $data['benchboost'],
                            'wildcard' => $data['wildcard'],
                            'triple' => $data['triple'],
                            'cutoff_time' => $data['cutoff_time'] ? strtotime($data['cutoff_time']) : null,
                        ]);
                    }
                }
            }
            
            // Create new matchweeks
            if ($request->has('new_matchweeks')) {
                foreach ($request->new_matchweeks as $matchweek => $data) {
                    FantasyTimeline::create([
                        'competition_id' => $competition_id,
                        'matchweek' => $matchweek,
                        'transfer' => $data['transfer'],
                        'max_same_club' => $data['max_same_club'],
                        'credit' => $data['credit'],
                        'benchboost' => $data['benchboost'],
                        'wildcard' => $data['wildcard'],
                        'triple' => $data['triple'],
                        'cutoff_time' => $data['cutoff_time'] ? strtotime($data['cutoff_time']) : null,
                        'status' => 'ACTIVE'
                    ]);
                }
                
                // Update total matchweeks in fantasy rules
                $fantasy = FantasyRules::where('competition_id', $competition_id)->first();
                if ($fantasy) {
                    $totalMatchweeks = FantasyTimeline::where('competition_id', $competition_id)->count();
                    $fantasy->matchweeks = $totalMatchweeks;
                    $fantasy->save();
                }
            }
            
            session()->flash('success', 'Fantasy rules updated successfully!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update fantasy rules: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.fantasy.edit', $competition_id);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->checkAuthorization(auth()->user(), ['competition.delete']);

        $competition = Competition::findOrFail($id);
        $competition->delete();
        session()->flash('success', 'Competition has been deleted.');
        return back();
    }

    public function updateStatus($competition_id, $matchweek)
    {
       try {
            $timeline = FantasyTimeline::where('competition_id', $competition_id)
                ->where('matchweek', $matchweek)
                ->firstOrFail();
            
            $timeline->status = 'DONE';
            $timeline->save();
            
            session()->flash('success', "Matchweek {$matchweek} status updated to DONE successfully!");
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update matchweek status: ' . $e->getMessage());
        }
        
        return redirect()->route('admin.fantasy.edit', $competition_id);
    }
}
