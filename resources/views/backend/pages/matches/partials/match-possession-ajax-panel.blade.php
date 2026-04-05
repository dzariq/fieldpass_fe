{{--
  $possessionMatch: App\Models\Matches
  $homeClubId, $awayClubId: int
  $homeName, $awayName: string
  $possessionSummary: array
  $canEdit: bool
  $showFullLogLink: bool (optional)
--}}
@php
    $pSum = $possessionSummary ?? [];
    $showFullLogLink = $showFullLogLink ?? true;
    $fmtPossDur = static function (int $sec): string {
        return sprintf('%d:%02d', intdiv($sec, 60), $sec % 60);
    };
    $playingSec = $possessionMatch->playingElapsedSeconds();
    $fpPossessionStatus = $possessionMatch->status ?? 'NOT_STARTED';
    $fpPossessionStatusLabels = [
        'NOT_STARTED' => __('Not started'),
        'ONGOING' => __('Ongoing'),
        'END' => __('Ended'),
        'POSTPONED' => __('Postponed'),
    ];
    $fpPossessionBadgeClass = match ($fpPossessionStatus) {
        'ONGOING' => 'badge-success',
        'END' => 'badge-dark',
        'POSTPONED' => 'badge-warning',
        default => 'badge-secondary',
    };
@endphp
<div
    class="possession-timer-panel fp-possession-ajax-root"
    id="fp-possession-ajax-root"
    data-match-id="{{ $possessionMatch->id }}"
    data-url-start="{{ route('admin.matches.record-start', $possessionMatch->id) }}"
    data-url-possession="{{ route('admin.matches.record-possession', $possessionMatch->id) }}"
    data-url-pause="{{ route('admin.matches.timer-pause', $possessionMatch->id) }}"
    data-url-resume="{{ route('admin.matches.timer-resume', $possessionMatch->id) }}"
    data-url-reset="{{ route('admin.matches.possession-reset', $possessionMatch->id) }}"
    data-url-details="{{ route('admin.matches.details', $possessionMatch->id) }}"
    data-home-club-id="{{ $homeClubId }}"
    data-away-club-id="{{ $awayClubId }}"
    data-home-name="{{ $homeName }}"
    data-away-name="{{ $awayName }}"
    data-csrf="{{ csrf_token() }}"
    data-playing-seconds="{{ $playingSec !== null ? $playingSec : '' }}"
    data-is-paused="{{ $possessionMatch->timer_pause_started_at ? '1' : '0' }}"
    data-started-at="{{ $possessionMatch->started_at ? $possessionMatch->started_at->toIso8601String() : '' }}"
    data-match-status="{{ $fpPossessionStatus }}"
    data-status-labels="{{ e(json_encode($fpPossessionStatusLabels, JSON_UNESCAPED_UNICODE)) }}"
>
    <div id="fp-possession-toast" class="small mb-2" style="display: none;" role="status"></div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <h5 class="mb-0">⏱️ {{ __('Match timer & ball possession') }}</h5>
        <span id="fp-match-status-badge" class="badge fp-match-status-badge {{ $fpPossessionBadgeClass }}">{{ $fpPossessionStatusLabels[$fpPossessionStatus] ?? $fpPossessionStatus }}</span>
    </div>
    <p class="possession-sub mb-0">{{ __('Start the match to begin the clock, then tap which team has the ball when it changes. Pause freezes the match clock.') }}</p>

    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3" style="gap: 12px;">
        <div>
            <div class="text-muted small text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.04em;">{{ __('Elapsed') }}</div>
            <div id="match-live-timer" class="match-timer-display">—</div>
            <div id="fp-timer-paused-label" class="small text-warning font-weight-bold" style="display: none;">{{ __('PAUSED') }}</div>
        </div>
        <div class="text-right small text-muted">
            <div id="fp-possession-kickoff-line">
                @if ($possessionMatch->started_at)
                    <strong>{{ __('Kickoff recorded') }}:</strong>
                    <span id="fp-kickoff-at">{{ $possessionMatch->started_at->format('Y-m-d H:i:s') }}</span>
                @else
                    {{ __('Kickoff not recorded yet.') }}
                @endif
            </div>
        </div>
    </div>

    @if ($canEdit)
        <div class="d-flex flex-wrap align-items-center mt-2" style="gap: 8px;">
            <button type="button" class="btn btn-primary font-weight-bold" id="fp-btn-match-start" @if ($possessionMatch->started_at) style="display: none;" @endif>
                {{ __('Record match start (now)') }}
            </button>
            <button type="button" class="btn btn-outline-warning font-weight-bold" id="fp-btn-timer-pause" @if (! $possessionMatch->started_at || $possessionMatch->timer_pause_started_at) style="display: none;" @endif>
                {{ __('Pause') }}
            </button>
            <button type="button" class="btn btn-outline-success font-weight-bold" id="fp-btn-timer-resume" @if (! $possessionMatch->started_at || ! $possessionMatch->timer_pause_started_at) style="display: none;" @endif>
                {{ __('Resume') }}
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="fp-btn-possession-reset" @if (! $possessionMatch->started_at && $possessionMatch->possessions->isEmpty()) style="display: none;" @endif>
                {{ __('Reset (clear timer & possession)') }}
            </button>
        </div>
    @endif

    <div class="possession-btn-row">
        @if ($canEdit)
            <button type="button" class="btn-possession-home" id="fp-btn-possession-home" @if (! $possessionMatch->started_at) disabled @endif>
                🏠 {{ __('Home ball') }} — {{ $homeName }}
            </button>
            <button type="button" class="btn-possession-away" id="fp-btn-possession-away" @if (! $possessionMatch->started_at) disabled @endif>
                ✈️ {{ __('Away ball') }} — {{ $awayName }}
            </button>
        @endif
        @if ($showFullLogLink)
            <a href="{{ route('admin.matches.details', $possessionMatch->id) }}" class="btn btn-outline-secondary btn-sm align-self-center">{{ __('Full log') }}</a>
        @endif
    </div>

    <div class="possession-mini-stats mb-0" id="fp-possession-mini-stats">
        <strong>{{ __('Approx. possession') }}:</strong>
        <span id="fp-mini-stats-inner">
            {{ $homeName }} {{ $fmtPossDur((int) ($pSum['home_seconds'] ?? 0)) }}
            @if (($pSum['home_pct'] ?? null) !== null)
                ({{ $pSum['home_pct'] }}%)
            @endif
            ·
            {{ $awayName }} {{ $fmtPossDur((int) ($pSum['away_seconds'] ?? 0)) }}
            @if (($pSum['away_pct'] ?? null) !== null)
                ({{ $pSum['away_pct'] }}%)
            @endif
            @if (($pSum['unknown_seconds'] ?? 0) > 0)
                · <span class="text-muted">{{ __('Before first switch') }}: {{ $fmtPossDur((int) $pSum['unknown_seconds']) }}</span>
            @endif
        </span>
    </div>

    <h6 class="text-secondary mt-3 mb-2">{{ __('Possession log') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
            <thead class="bg-light">
                <tr>
                    <th>{{ __('When') }}</th>
                    <th>{{ __('Team') }}</th>
                    <th>{{ __('Recorded by') }}</th>
                </tr>
            </thead>
            <tbody id="fp-possession-log-tbody">
                @forelse ($possessionMatch->possessions as $row)
                    <tr>
                        <td>{{ $row->event_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $row->club->name ?? '—' }}</td>
                        <td>{{ $row->admin->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr class="fp-possession-empty">
                        <td colspan="3" class="text-muted text-center">{{ __('No possession entries yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
