<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Competition;
use Illuminate\Http\JsonResponse;


class AjaxController extends Controller
{
    public function getClubsByCompetition($competition_id): JsonResponse
    {
        $this->checkAuthorization(auth()->user(), ['match.view']);

        $competitionId = (int) $competition_id;
        $competition = Competition::find($competitionId);
        if (!$competition) {
            return response()->json([], 404);
        }

        // Scope association admins: only allow competitions in their association(s)
        $admin = auth()->user();
        $associationIds = $admin ? $admin->associations()->pluck('association.id') : collect();
        if ($associationIds->count() > 0 && !auth()->user()->can('association.create')) {
            if (!in_array((int) $competition->association_id, $associationIds->map(fn ($id) => (int) $id)->all(), true)) {
                return response()->json([], 403);
            }
        }

        // Only active clubs in this competition
        $clubs = Club::whereHas('competitions', function ($query) use ($competitionId) {
            // Be explicit: filter by competition table + pivot status
            $query->where('competition.id', $competitionId)
                ->where('competition_club.status', 'ACTIVE');
        })
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($clubs);
    }
}
