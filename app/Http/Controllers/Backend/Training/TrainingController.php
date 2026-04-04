<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend\Training;

use App\Models\Player;
use App\Models\PlayerTraining;
use Carbon\Carbon;
use App\Models\Competition;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrainingAttributesRequest;
use App\Http\Requests\PlayerTrainingRequest;
use App\Models\Association;
use App\Models\Club;
use App\Models\TrainingAttribute;
use App\Models\Matches;
use App\Models\Admin;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    private function currentAdminClubId(): ?int
    {
        $admin = Admin::find(Auth::id());

        return $admin?->primaryClubId();
    }

    public function index(): Renderable
    {
        $clubId = $this->currentAdminClubId();
        if ($clubId === null) {
            return view('backend.training.training', [
                'players' => collect(),
                'trainingAttributes' => collect(),
                'currentTrainings' => collect(),
                'overdueTrainings' => collect(),
                'trainingClubMissing' => true,
            ]);
        }

        // Get all players for this club
        $players = Player::whereHas('clubs', function ($query) use ($clubId) {
            $query->where('club.id', $clubId);
        })
            ->select('id', 'name', 'identity_number')
            ->orderBy('identity_number')
            ->get();

        // Get active training attributes
        $trainingAttributes = TrainingAttribute::where('club_id', $clubId)
            ->where('status', 'active')
            ->select('id', 'name')
            ->get();

        // Get current training data for all players
        $currentTrainings = PlayerTraining::whereIn('player_id', $players->pluck('id'))
            ->with(['trainingAttribute:id,name', 'player:id,name'])
            ->get()
            ->groupBy('player_id');

        // Check for overdue trainings
        $overdueTrainings = PlayerTraining::whereIn('player_id', $players->pluck('id'))
            ->where('end_date', '<', Carbon::today())
            ->whereNull('score')
            ->pluck('player_id')
            ->unique();

        return view('backend.training.training', compact(
            'players',
            'trainingAttributes',
            'currentTrainings',
            'overdueTrainings'
        ));
    }
    public function attributes(): Renderable
    {
        $clubId = $this->currentAdminClubId();
        $attributes = $clubId === null
            ? collect()
            : TrainingAttribute::where('club_id', $clubId)
                ->orderBy('id')
                ->get();

        return view('backend.training.attributes', compact('attributes', 'clubId'));
    }

    public function submit(PlayerTrainingRequest $request): RedirectResponse
    {
        try {
            $clubId = $this->currentAdminClubId();
            if ($clubId === null) {
                return redirect()->back()->with('error', __('No club is assigned to your account. Training cannot be saved.'));
            }

            DB::transaction(function () use ($request, $clubId) {
                foreach ($request->input('player_trainings') as $trainingData) {
                    // Verify player belongs to the club (many-to-many)
                    $player = Player::whereHas('clubs', function ($query) use ($clubId) {
                        $query->where('club.id', $clubId);
                    })
                        ->where('id', $trainingData['player_id'])
                        ->first();

                    if (!$player) {
                        throw new \Exception('Invalid player selected.');
                    }

                    // Create or update player training
                    PlayerTraining::updateOrCreate(
                        [
                            'player_id' => $trainingData['player_id'],
                            'training_attribute_id' => $trainingData['training_attribute_id'],
                        ],
                        [
                            'start_date' => $trainingData['start_date'],
                            'end_date' => $trainingData['end_date'],
                            'score' => $trainingData['score'] ?? null,
                            'message' => $trainingData['message'] ?? null,
                        ]
                    );
                }
            });

            return redirect()->back()->with('success', 'Player trainings updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating player trainings: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'An error occurred while updating player trainings.');
        }
    }

    public function attributes_submit(TrainingAttributesRequest $request): RedirectResponse
    {
        try {
            $clubId = $this->currentAdminClubId();
            if ($clubId === null) {
                return redirect()->back()->with('error', __('No valid club is linked to your account. Ask an administrator to assign you to a club in admin_club, or fix a broken link if the club was deleted.'));
            }

            if (! Club::query()->whereKey($clubId)->exists()) {
                return redirect()->back()->with('error', __('The club linked to your account was not found. Ask an administrator to update your club assignment.'));
            }

            DB::transaction(function () use ($request, $clubId) {
                // Check if this is first time (no existing attributes)
                $existingAttributesCount = TrainingAttribute::where('club_id', $clubId)->count();
                $isFirstTime = $existingAttributesCount === 0;

                if ($isFirstTime) {
                    // First time: Just create all new attributes
                    foreach ($request->getAttributes() as $attributeData) {
                        TrainingAttribute::create([
                            'name' => $attributeData['name'],
                            'status' => $attributeData['status'],
                            'club_id' => $clubId,
                        ]);
                    }
                } else {
                    // Not first time: Handle updates and deletions
                    $submittedIds = collect($request->getAttributes())
                        ->pluck('id')
                        ->filter()
                        ->toArray();

                    // Delete attributes that are not in the submitted data
                    if (!empty($submittedIds)) {
                        TrainingAttribute::where('club_id', $clubId)
                            ->whereNotIn('id', $submittedIds)
                            ->delete();
                    }

                    // Create or update attributes
                    foreach ($request->getAttributes() as $attributeData) {
                        if (isset($attributeData['id']) && $attributeData['id']) {
                            // Update existing attribute
                            TrainingAttribute::where('id', $attributeData['id'])
                                ->where('club_id', $clubId)
                                ->update([
                                    'name' => $attributeData['name'],
                                    'status' => $attributeData['status'],
                                ]);
                        } else {
                            // Create new attribute
                            TrainingAttribute::create([
                                'name' => $attributeData['name'],
                                'status' => $attributeData['status'],
                                'club_id' => $clubId,
                            ]);
                        }
                    }
                }
            });

            return redirect()->back()->with('success', 'Training attributes updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating training attributes: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $msg = config('app.debug')
                ? $e->getMessage()
                : __('An error occurred while updating training attributes.');

            return redirect()->back()->with('error', $msg);
        }
    }
}
