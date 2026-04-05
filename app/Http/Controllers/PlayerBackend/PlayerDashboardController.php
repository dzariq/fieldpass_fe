<?php

declare(strict_types=1);

namespace App\Http\Controllers\PlayerBackend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerRequest;
use App\Models\Player;
use App\Services\PlayerClubHistoryPerformanceService;
use App\Models\PlayerClubHistory;
use App\Models\PlayerContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlayerDashboardController extends Controller
{
    public function details($id)
    {
        // Dummy data
        $performanceData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            'goals' => [2, 4, 1, 3, 5],
            'assists' => [1, 3, 2, 2, 4],
        ];

        $history = [
            ['match_date' => '2025-01-10', 'opponent' => 'Team A', 'goals' => 1, 'assists' => 0, 'played' => true],
            ['match_date' => '2025-01-24', 'opponent' => 'Team B', 'goals' => 0, 'assists' => 1, 'played' => true],
            ['match_date' => '2025-02-14', 'opponent' => 'Team C', 'goals' => 2, 'assists' => 1, 'played' => true],
            ['match_date' => '2025-03-03', 'opponent' => 'Team D', 'goals' => 0, 'assists' => 0, 'played' => false],
        ];

        $player = Player::find($id);

        return view('playerbackend.pages.dashboard.details', compact('performanceData', 'history', 'player'));
    }

    public function index()
    {

        // player data
        $player = Auth::guard('player')->user();

        $currentContract = null;
        $currentClub = null;
        $clubHistoryRows = [];
        $matchPerformance = [
            'available' => false,
            'totals' => [],
            'recent' => [],
            'message' => '',
        ];

        if ($player) {
            $currentContract = PlayerContract::query()
                ->with('club')
                ->where('player_id', $player->id)
                ->where('status', 'active')
                ->orderByDesc('end_date')
                ->orderByDesc('start_date')
                ->first();

            $currentClub = $currentContract?->club;
            if (! $currentClub) {
                // Fallback when contract isn't present (legacy data)
                $currentClub = $player->clubs()->first();
            }

            $perfSvc = app(PlayerClubHistoryPerformanceService::class);
            $clubHistoryRows = $perfSvc->clubHistoryForPlayer($player->id);
            $matchPerformance = $perfSvc->matchPerformanceSummary($player->id);
        }

        return view(
            'playerbackend.pages.dashboard.index',
            [
                'player' => $player,
                'currentClub' => $currentClub,
                'currentContract' => $currentContract,
                'clubHistoryRows' => $clubHistoryRows,
                'matchPerformance' => $matchPerformance,
            ]
        );
    }

    public function update(PlayerRequest $request, int $playerId): RedirectResponse
    {
        $player = Player::findOrFail($playerId);

        // Only update fields that are sent in the request
        $player->name = $request->name;

        // Only update email if provided
        if ($request->filled('email')) {
            $player->email = $request->email;
        }

        // Only update username if provided
        if ($request->filled('username')) {
            $player->username = $request->username;
        }

        $player->identity_number = $request->identity_number;

        // Only update phone if provided
        if ($request->filled('phone')) {
            $player->phone = $request->phone;
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $player->password = Hash::make($request->password);
        }

        // Only update status if provided
        if ($request->filled('status')) {
            $player->status = $request->status;
        }

        if ($request->hasFile('avatar')) {
            $request->validate([
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:1024',
            ]);

            $file = $request->file('avatar');
            $filename = time().'_'.$file->getClientOriginalName();
            $destination = public_path('avatars');

            if (! file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            // Delete old avatar if exists
            if ($player->avatar && file_exists(public_path($player->avatar))) {
                unlink(public_path($player->avatar));
            }

            $file->move($destination, $filename);
            $player->avatar = 'avatars/'.$filename;
        }

        $player->save();

        // Only update roles if provided
        if ($request->has('roles')) {
            $player->roles()->detach();
            $player->assignRole($request->roles);
        }

        // Only update clubs if provided
        if ($request->has('club_ids') && ! empty($request->club_ids)) {
            $oldClubIds = $player->clubs()->pluck('club.id')->map(fn ($id) => (int) $id)->all();
            $player->clubs()->detach();
            $player->clubs()->sync($request->club_ids);
            $newClubIds = array_map('intval', $request->club_ids);
            foreach (array_diff($oldClubIds, $newClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'removed', null, __('Player profile — removed from club'));
            }
            foreach (array_diff($newClubIds, $oldClubIds) as $cid) {
                PlayerClubHistory::record($player->id, $cid, 'assigned', null, __('Player profile — joined club'));
            }
        }

        if ($player->status == 'INVITED') {
            $this->triggerEvent([
                'action' => 'player_invitation',
                'code' => $player->code,
                'email' => $player->email,
                'name' => $player->name,
            ]);
        }

        session()->flash('success', 'Player profile has been updated successfully.');

        return back();
    }
}
