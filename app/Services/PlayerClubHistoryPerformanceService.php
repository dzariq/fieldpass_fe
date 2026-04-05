<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PlayerClubHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlayerClubHistoryPerformanceService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function clubHistoryForPlayer(int $playerId, int $limit = 200): array
    {
        return PlayerClubHistory::query()
            ->where('player_id', $playerId)
            ->with(['club:id,name', 'admin:id,name'])
            ->orderByDesc('event_at')
            ->limit($limit)
            ->get()
            ->map(function (PlayerClubHistory $h) {
                $label = match ($h->event_type) {
                    'assigned' => __('Joined club'),
                    'terminated' => __('Contract terminated'),
                    'removed' => __('Removed from club'),
                    default => $h->event_type,
                };

                return [
                    'event_type' => $h->event_type,
                    'event_label' => $label,
                    'club_name' => $h->club?->name ?? '—',
                    'event_at' => $h->event_at?->format('Y-m-d H:i'),
                    'remark' => $h->remark,
                    'admin_name' => $h->admin?->name,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function matchPerformanceSummary(int $playerId): array
    {
        if (! Schema::hasTable('match_events') || ! Schema::hasTable('match') || ! Schema::hasTable('club')) {
            return [
                'available' => false,
                'totals' => [],
                'recent' => [],
                'message' => __('Match statistics are not available.'),
            ];
        }

        $totals = DB::table('match_events')
            ->where('player_id', $playerId)
            ->selectRaw('event_type, COUNT(*) as c')
            ->groupBy('event_type')
            ->pluck('c', 'event_type')
            ->all();

        $select = [
            'me.event_type',
            'me.minute_in_match',
            'm.date',
            'm.home_club_id',
            'm.away_club_id',
            'me.club_id',
            'hc.name as home_name',
            'ac.name as away_name',
        ];

        $query = DB::table('match_events as me')
            ->join('match as m', 'm.id', '=', 'me.match_id')
            ->leftJoin('club as hc', 'hc.id', '=', 'm.home_club_id')
            ->leftJoin('club as ac', 'ac.id', '=', 'm.away_club_id')
            ->where('me.player_id', $playerId)
            ->orderByDesc('m.date')
            ->orderByDesc('me.minute_in_match')
            ->limit(40);

        if (Schema::hasTable('competition')) {
            $query->leftJoin('competition as comp', 'comp.id', '=', 'm.competition_id');
            $select[] = 'comp.name as competition_name';
        }

        $recent = $query->select($select)->get()->map(function ($row) {
            $opponent = '—';
            $cid = (int) $row->club_id;
            if ($cid === (int) $row->home_club_id) {
                $opponent = (string) ($row->away_name ?? '—');
            } elseif ($cid === (int) $row->away_club_id) {
                $opponent = (string) ($row->home_name ?? '—');
            }

            $ts = $row->date;
            $matchDate = null;
            if (is_numeric($ts)) {
                $matchDate = date('Y-m-d', (int) $ts);
            } elseif (is_string($ts) && $ts !== '') {
                $parsed = strtotime($ts);
                $matchDate = $parsed ? date('Y-m-d', $parsed) : null;
            }

            return [
                'event_type' => $row->event_type,
                'event_label' => $this->matchEventTypeLabel((string) $row->event_type),
                'minute_in_match' => $row->minute_in_match,
                'match_date' => $matchDate,
                'competition' => isset($row->competition_name) ? $row->competition_name : null,
                'opponent' => $opponent,
            ];
        });

        $out = [
            'available' => true,
            'totals' => $totals,
            'recent' => $recent->values()->all(),
        ];

        $chart = $this->buildPlayerMatchEventsChartByMonth($playerId);
        if ($chart !== null) {
            $out['points_by_month_chart'] = $chart;
        }

        return $out;
    }

    private function matchEventTypeLabel(string $eventType): string
    {
        return match ($eventType) {
            'goal' => __('Goal'),
            'assist' => __('Assist'),
            'sub_in' => __('Substituted in'),
            'sub_out' => __('Substituted out'),
            'own_goal' => __('Own goal'),
            'yellow_card' => __('Yellow card'),
            'red_card' => __('Red card'),
            default => $eventType,
        };
    }

    /**
     * @return array{labels: list<string>, datasets: list<array<string, mixed>>}|null
     */
    private function buildPlayerMatchEventsChartByMonth(int $playerId): ?array
    {
        if (! Schema::hasTable('match_events') || ! Schema::hasTable('match')) {
            return null;
        }

        $rows = DB::table('match_events as me')
            ->join('match as m', 'm.id', '=', 'me.match_id')
            ->where('me.player_id', $playerId)
            ->select(['me.event_type', 'm.date as match_date_raw'])
            ->get();

        $byMonth = [];
        foreach ($rows as $row) {
            $ym = $this->resolveMatchDateToYearMonth($row->match_date_raw);
            if ($ym === null) {
                continue;
            }
            $et = (string) $row->event_type;
            if (! isset($byMonth[$ym])) {
                $byMonth[$ym] = [];
            }
            $byMonth[$ym][$et] = ($byMonth[$ym][$et] ?? 0) + 1;
        }

        if ($byMonth === []) {
            return null;
        }

        ksort($byMonth);
        $labelsYm = array_keys($byMonth);

        $typeGrand = [];
        foreach ($byMonth as $counts) {
            foreach ($counts as $t => $n) {
                $typeGrand[$t] = ($typeGrand[$t] ?? 0) + $n;
            }
        }
        arsort($typeGrand);
        $orderedTypes = array_slice(array_keys($typeGrand), 0, 10);

        $palette = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796', '#6610f2', '#fd7e14', '#20c9a6', '#5a5c69'];

        $displayLabels = array_map(function (string $ym) {
            try {
                return Carbon::parse($ym.'-01')->format('M Y');
            } catch (\Throwable) {
                return $ym;
            }
        }, $labelsYm);

        $datasets = [];
        foreach ($orderedTypes as $i => $type) {
            $data = [];
            foreach ($labelsYm as $ym) {
                $data[] = (int) ($byMonth[$ym][$type] ?? 0);
            }
            $datasets[] = [
                'label' => $this->matchEventTypeLabel($type),
                'data' => $data,
                'borderColor' => $palette[$i % count($palette)],
                'fill' => false,
            ];
        }

        return [
            'labels' => $displayLabels,
            'datasets' => $datasets,
        ];
    }

    private function resolveMatchDateToYearMonth(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            return date('Y-m', (int) $raw);
        }
        $parsed = strtotime((string) $raw);

        return $parsed ? date('Y-m', $parsed) : null;
    }
}
