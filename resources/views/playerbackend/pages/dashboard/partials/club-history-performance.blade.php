@php
    $perf = $matchPerformance ?? ['available' => false, 'totals' => [], 'recent' => []];
    $chart = $perf['points_by_month_chart'] ?? null;
    $historyRows = $clubHistoryRows ?? [];
@endphp

<div class="card profile-card mt-4">
    <div class="card-body" style="padding: 30px 40px;">
        <div class="section-title mb-3">
            <i class="fa fa-history"></i> {{ __('Club history') }}
        </div>
        @if(count($historyRows))
            <div class="table-responsive mb-0" style="max-height: 360px; overflow-y: auto;">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Event') }}</th>
                            <th>{{ __('Club') }}</th>
                            <th>{{ __('By') }}</th>
                            <th>{{ __('Note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyRows as $h)
                            <tr>
                                <td>{{ $h['event_at'] ?? '—' }}</td>
                                <td>{{ $h['event_label'] ?? '—' }}</td>
                                <td>{{ $h['club_name'] ?? '—' }}</td>
                                <td>{{ $h['admin_name'] ?? '—' }}</td>
                                <td class="small">{{ $h['remark'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0">{{ __('No club history recorded yet.') }}</p>
        @endif
    </div>
</div>

<div class="card profile-card mt-4">
    <div class="card-body" style="padding: 30px 40px;">
        <div class="section-title mb-3">
            <i class="fa fa-futbol"></i> {{ __('Match performance') }}
        </div>
        @if(empty($perf['available']))
            <p class="text-muted small mb-0">{{ $perf['message'] ?? __('Match statistics are not available.') }}</p>
        @else
            <p class="small mb-2"><strong>{{ __('Totals (all matches)') }}:</strong>
                @if(!empty($perf['totals']) && count($perf['totals']))
                    @foreach($perf['totals'] as $type => $count)
                        {{ str_replace('_', ' ', (string) $type) }}: {{ $count }}@if(!$loop->last), @endif
                    @endforeach
                @else
                    —
                @endif
            </p>

            @if($chart && !empty($chart['labels']) && !empty($chart['datasets']))
                <h6 class="text-secondary border-bottom pb-1 mt-3 mb-2">{{ __('Match events by month') }}</h6>
                <p class="text-muted small mb-2">{{ __('Count of recorded events per calendar month (match date). Up to 10 event types by total volume.') }}</p>
                <div class="player-dashboard-perf-chart-wrap mb-4" style="position:relative;height:260px;max-width:100%;">
                    <canvas id="playerDashboardPerfChart"></canvas>
                </div>
            @endif

            @if(!empty($perf['recent']))
                <h6 class="text-secondary border-bottom pb-1 mt-2 mb-2">{{ __('Recent match events') }}</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Competition') }}</th>
                                <th>{{ __('Opponent') }}</th>
                                <th>{{ __('Event') }}</th>
                                <th>{{ __('Min') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($perf['recent'] as $r)
                                <tr>
                                    <td>{{ $r['match_date'] ?? '—' }}</td>
                                    <td>{{ $r['competition'] ?? '—' }}</td>
                                    <td>{{ $r['opponent'] ?? '—' }}</td>
                                    <td>{{ $r['event_label'] ?? $r['event_type'] ?? '—' }}</td>
                                    <td>{{ $r['minute_in_match'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small mb-0">{{ __('No match events yet.') }}</p>
            @endif
        @endif
    </div>
</div>
