<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Matches;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;  // <- Tambah line ni

class DashboardController extends Controller
{
    // Updated Controller - Club Dashboard Section
    public function index()
    {
        $this->checkAuthorization(auth()->user(), ['dashboard.view']);

        $admin = auth()->user();
        $admin_obj = Admin::query()->with(['associations', 'clubs'])->findOrFail($admin->id);
        // Use loaded relations so IDs resolve reliably (query pluck('club.id') can be empty on some DB setups).
        $associationIds = $admin_obj->associations->pluck('id')->unique()->values()->all();
        $clubIds = $admin_obj->clubs->pluck('id')->unique()->values()->all();

        if (count($associationIds) > 0) {
            // ASSOCIATION ADMIN DASHBOARD
            $associationId = $associationIds[0];

            // Get all active competitions for this association
            $competitions = DB::table('competition')
                ->where('association_id', $associationId)
                ->where('status', 'ACTIVE')
                ->orderBy('type')
                ->orderBy('name')
                ->get();

            $competitionsByType = $competitions->groupBy('type');
            $totalActive = $competitions->count();
            $leagues = $competitions->where('type', 'LEAGUE')->count();
            $cups = $competitions->where('type', 'CUP')->count();
            $tournaments = $competitions->where('type', 'TOURNAMENT')->count();
            $total_admins = Admin::count();
            $total_roles = Role::count();
            $total_permissions = Permission::count();

            $today = time();

            // Get PASSED fixtures with lineup status
            $passedFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->leftJoin('match_players as home_lineup', function ($join) {
                    $join->on('match.id', '=', 'home_lineup.match_id')
                        ->on('match.home_club_id', '=', 'home_lineup.club_id');
                })
                ->leftJoin('match_players as away_lineup', function ($join) {
                    $join->on('match.id', '=', 'away_lineup.match_id')
                        ->on('match.away_club_id', '=', 'away_lineup.club_id');
                })
                ->where('home_club.association_id', $associationId)
                ->where('match.date', '<', $today)
                ->where('match.status', 'END')
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'match.home_score',
                    'match.away_score',
                    'home_club.name as home_club_name',
                    'home_club.avatar as home_club_avatar',
                    'away_club.name as away_club_name',
                    'away_club.avatar as away_club_avatar',
                    'competition.name as competition_name',
                    'competition.type as competition_type',
                    'competition.id as competition_id',
                    DB::raw('CASE WHEN home_lineup.match_id IS NOT NULL THEN 1 ELSE 0 END as home_lineup_submitted'),
                    DB::raw('CASE WHEN away_lineup.match_id IS NOT NULL THEN 1 ELSE 0 END as away_lineup_submitted')
                )
                ->orderBy('match.date', 'desc')
                ->limit(20)
                ->get();

            // Get UPCOMING fixtures with lineup status
            $upcomingFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->leftJoin('match_players as home_lineup', function ($join) {
                    $join->on('match.id', '=', 'home_lineup.match_id')
                        ->on('match.home_club_id', '=', 'home_lineup.club_id');
                })
                ->leftJoin('match_players as away_lineup', function ($join) {
                    $join->on('match.id', '=', 'away_lineup.match_id')
                        ->on('match.away_club_id', '=', 'away_lineup.club_id');
                })
                ->where('home_club.association_id', $associationId)
                ->where('match.date', '>=', $today)
                ->where('match.status', 'NOT_STARTED')
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'home_club.name as home_club_name',
                    'home_club.avatar as home_club_avatar',
                    'away_club.name as away_club_name',
                    'away_club.avatar as away_club_avatar',
                    'competition.name as competition_name',
                    'competition.type as competition_type',
                    'competition.id as competition_id',
                    DB::raw('CASE WHEN home_lineup.match_id IS NOT NULL THEN 1 ELSE 0 END as home_lineup_submitted'),
                    DB::raw('CASE WHEN away_lineup.match_id IS NOT NULL THEN 1 ELSE 0 END as away_lineup_submitted')
                )
                ->orderBy('match.date', 'asc')
                ->limit(20)
                ->get();

            // Group fixtures by competition
            $passedFixturesByCompetition = $passedFixtures->groupBy('competition_name');
            $upcomingFixturesByCompetition = $upcomingFixtures->groupBy('competition_name');

            return view('backend.pages.dashboard.association', compact(
                'competitions',
                'competitionsByType',
                'totalActive',
                'leagues',
                'cups',
                'tournaments',
                'total_admins',
                'total_roles',
                'total_permissions',
                'passedFixturesByCompetition',
                'upcomingFixturesByCompetition'
            ));
        }

        if (count($clubIds) > 0) {
            // CLUB ADMIN DASHBOARD - Updated Logic

            // Get club information
            $clubId = $clubIds[0]; // Take first club if multiple
            $club = DB::table('club')->where('id', $clubId)->first();

            if ($club !== null) {

            // Get club statistics
            $total_admins = Admin::count();
            $total_roles = Role::count();
            $total_permissions = Permission::count();

            $today = time();

            // Get PASSED fixtures for this specific club (both home and away)
            $passedFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->where(function ($query) use ($clubId) {
                    $query->where('match.home_club_id', $clubId)
                        ->orWhere('match.away_club_id', $clubId);
                })
                ->where('match.date', '<', $today)
                ->where('match.status', 'END')
                ->select(
                    'match.id',
                    'match.date',
                    'match.matchweek',
                    'match.home_score',
                    'match.away_score',
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
                ->orderBy('match.date', 'desc')
                ->limit(20)
                ->get();

            // Get UPCOMING fixtures for this specific club
            $upcomingFixtures = DB::table('match')
                ->join('club as home_club', 'match.home_club_id', '=', 'home_club.id')
                ->join('club as away_club', 'match.away_club_id', '=', 'away_club.id')
                ->join('competition', 'match.competition_id', '=', 'competition.id')
                ->where(function ($query) use ($clubId) {
                    $query->where('match.home_club_id', $clubId)
                        ->orWhere('match.away_club_id', $clubId);
                })
                ->where('match.date', '>=', $today)
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
                ->limit(20)
                ->get();

            // Group fixtures by competition
            $passedFixturesByCompetition = $passedFixtures->groupBy('competition_name');
            $upcomingFixturesByCompetition = $upcomingFixtures->groupBy('competition_name');

            // Club statistics
            $totalPassedMatches = $passedFixtures->count();
            $totalUpcomingMatches = $upcomingFixtures->count();
            $homeMatches = $passedFixtures->where('home_club_id', $clubId)->count();
            $awayMatches = $passedFixtures->where('away_club_id', $clubId)->count();

            // Wins, draws, losses for this club
            $wins = 0;
            $draws = 0;
            $losses = 0;

            foreach ($passedFixtures as $fixture) {
                if ($fixture->home_club_id == $clubId) {
                    // Club was playing at home
                    if ($fixture->home_score > $fixture->away_score) {
                        $wins++;
                    } elseif ($fixture->home_score == $fixture->away_score) {
                        $draws++;
                    } else {
                        $losses++;
                    }
                } else {
                    // Club was playing away
                    if ($fixture->away_score > $fixture->home_score) {
                        $wins++;
                    } elseif ($fixture->away_score == $fixture->home_score) {
                        $draws++;
                    } else {
                        $losses++;
                    }
                }
            }

            return view('backend.pages.dashboard.club', compact(
                'club',
                'total_admins',
                'total_roles',
                'total_permissions',
                'passedFixturesByCompetition',
                'upcomingFixturesByCompetition',
                'totalPassedMatches',
                'totalUpcomingMatches',
                'homeMatches',
                'awayMatches',
                'wins',
                'draws',
                'losses',
                'clubId'
            ));
            }
        }

        // SUPER ADMIN DASHBOARD (any admin without association/club scope still lands here; demo UI is permission-gated in the view)
        $total_admins = Admin::count();
        $total_roles = Role::count();
        $total_permissions = Permission::count();

        $demoEnabled = DB::table('demo_data_runs')
            ->whereIn('key', DemoDataController::allDemoKeys())
            ->where('enabled', 1)
            ->exists();

        return view('backend.pages.dashboard.index', compact(
            'total_admins',
            'total_roles',
            'total_permissions',
            'demoEnabled'
        ));
    }
}
