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
    $homeClub = $possessionMatch->home_club ?? null;
    $awayClub = $possessionMatch->away_club ?? null;
    $fpClubCode = static function (?object $club, string $fallbackName): string {
        $n = trim((string) ($club?->name ?? $fallbackName));
        if ($n === '') {
            return '—';
        }
        $parts = preg_split('/\s+/u', $n, -1, PREG_SPLIT_NO_EMPTY);
        if (count($parts) >= 2) {
            $code = '';
            foreach ($parts as $w) {
                $code .= mb_strtoupper(mb_substr($w, 0, 1));
                if (mb_strlen($code) >= 3) {
                    break;
                }
            }

            return mb_substr($code, 0, 3);
        }

        return mb_strtoupper(mb_substr($n, 0, min(3, mb_strlen($n))));
    };
    $homeCode = $fpClubCode($homeClub, $homeName);
    $awayCode = $fpClubCode($awayClub, $awayName);
    $homeAvatarUrl = ($homeClub && ($homeClub->avatar ?? null)) ? asset($homeClub->avatar) : null;
    $awayAvatarUrl = ($awayClub && ($awayClub->avatar ?? null)) ? asset($awayClub->avatar) : null;
    $hPct = $pSum['home_pct'] ?? null;
    $aPct = $pSum['away_pct'] ?? null;
    $hasSplitPct = $hPct !== null && $aPct !== null;
    $donutShare = $hasSplitPct ? max(0, min(1, (float) $hPct / 100)) : 0.5;
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
@once
<style>
    .fp-possession-log-wrap {
        max-height: min(360px, 45vh);
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    .fp-possession-log-wrap .table {
        margin-bottom: 0;
    }
    .fp-possession-log-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #f8f9fa;
        box-shadow: 0 1px 0 #dee2e6;
    }
    .fp-possession-viz {
        --fp-poss-home: #2563eb;
        --fp-poss-away: #6d28d9;
        margin-top: 16px;
        padding: 14px 16px 16px;
        max-width: 440px;
        margin-left: auto;
        margin-right: auto;
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
    }
    .fp-possession-viz-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 12px;
        margin-bottom: 4px;
        border-bottom: 1px dashed #cbd5e1;
    }
    .fp-possession-viz-team {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }
    .fp-possession-viz-team--away {
        flex-direction: row-reverse;
        text-align: right;
    }
    .fp-possession-viz-team img {
        width: 36px;
        height: 36px;
        object-fit: contain;
        border-radius: 6px;
        background: #f8fafc;
        flex-shrink: 0;
    }
    .fp-possession-viz-code {
        font-weight: 800;
        font-size: 0.95rem;
        letter-spacing: 0.06em;
        color: #0f172a;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fp-possession-viz-title {
        text-align: center;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        margin: 10px 0 12px;
    }
    .fp-possession-viz-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px 12px;
        flex-wrap: wrap;
    }
    .fp-poss-pct {
        flex: 0 1 auto;
        min-width: 4.5rem;
        text-align: center;
        font-variant-numeric: tabular-nums;
    }
    .fp-poss-pct--home {
        text-align: right;
    }
    .fp-poss-pct--away {
        text-align: left;
    }
    .fp-poss-pct-num {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1;
        color: #0f172a;
    }
    .fp-poss-pct-sup {
        font-size: 0.65rem;
        font-weight: 700;
        vertical-align: super;
        margin-left: 1px;
        color: #475569;
    }
    .fp-poss-donut-wrap {
        position: relative;
        width: 148px;
        height: 148px;
        flex-shrink: 0;
    }
    .fp-poss-donut-ring {
        --home-share: 0.5;
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: conic-gradient(
            var(--fp-poss-home) 0turn calc(var(--home-share) * 1turn),
            var(--fp-poss-away) 0 1turn
        );
    }
    .fp-poss-donut-ring.fp-poss-donut-ring--empty {
        background: conic-gradient(#e2e8f0 0turn 0.5turn, #cbd5e1 0.5turn 1turn);
    }
    .fp-poss-donut-hole {
        position: absolute;
        inset: 20px;
        border-radius: 50%;
        background: #fff;
        z-index: 1;
    }
    .fp-poss-donut-center {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        width: 92px;
        height: 92px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
    }
    .fp-poss-donut-center img {
        width: 34px;
        height: 34px;
        object-fit: contain;
    }
    .fp-poss-donut-divider {
        width: 1px;
        height: 28px;
        background: #cbd5e1;
        flex-shrink: 0;
    }
    .fp-poss-donut-ph {
        width: 34px;
        height: 34px;
        border-radius: 6px;
        background: #f1f5f9;
        font-size: 0.65rem;
        font-weight: 800;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endonce
<div
    class="possession-timer-panel fp-possession-ajax-root"
    id="fp-possession-ajax-root"
    data-match-id="{{ $possessionMatch->id }}"
    data-url-start="{{ route('admin.matches.record-start', $possessionMatch->id) }}"
    data-url-possession="{{ route('admin.matches.record-possession', $possessionMatch->id) }}"
    data-url-pause="{{ route('admin.matches.timer-pause', $possessionMatch->id) }}"
    data-url-resume="{{ route('admin.matches.timer-resume', $possessionMatch->id) }}"
    data-url-reset="{{ route('admin.matches.possession-reset', $possessionMatch->id) }}"
    data-url-status-end="{{ route('admin.matches.status-end', $possessionMatch->id) }}"
    data-url-status-ongoing="{{ route('admin.matches.status-ongoing', $possessionMatch->id) }}"
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
    data-possession-summary="{{ e(json_encode($pSum, JSON_UNESCAPED_UNICODE)) }}"
>
    <div id="fp-possession-toast" class="small mb-2" style="display: none;" role="status"></div>

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
        <h5 class="mb-0">⏱️ {{ __('Match timer & ball possession') }}</h5>
        <span id="fp-match-status-badge" class="badge fp-match-status-badge {{ $fpPossessionBadgeClass }}">{{ $fpPossessionStatusLabels[$fpPossessionStatus] ?? $fpPossessionStatus }}</span>
    </div>
    <p class="possession-sub mb-0">{{ __('Start the match to begin the clock, then tap which team has the ball when it changes. Use “ball out of play” when neither team has possession while the clock keeps running. Pause freezes the match clock.') }}</p>

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
            <button type="button" class="btn btn-outline-warning font-weight-bold" id="fp-btn-timer-pause" @if (! $possessionMatch->started_at || $possessionMatch->timer_pause_started_at || $fpPossessionStatus !== 'ONGOING') style="display: none;" @endif>
                {{ __('Pause') }}
            </button>
            <button type="button" class="btn btn-outline-success font-weight-bold" id="fp-btn-timer-resume" @if (! $possessionMatch->started_at || ! $possessionMatch->timer_pause_started_at || $fpPossessionStatus !== 'ONGOING') style="display: none;" @endif>
                {{ __('Resume') }}
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" id="fp-btn-possession-reset" @if (! $possessionMatch->started_at && $possessionMatch->possessions->isEmpty()) style="display: none;" @endif>
                {{ __('Reset (clear timer & possession)') }}
            </button>
            <button type="button" class="btn btn-dark font-weight-bold" id="fp-btn-match-end" @if ($fpPossessionStatus !== 'ONGOING') style="display: none;" @endif>
                {{ __('End match') }}
            </button>
            <button type="button" class="btn btn-outline-success font-weight-bold" id="fp-btn-match-reopen" @if ($fpPossessionStatus !== 'END') style="display: none;" @endif>
                {{ __('Mark ongoing again') }}
            </button>
        </div>
    @endif

    <div class="possession-btn-row">
        @if ($canEdit)
            <button type="button" class="btn-possession-home" id="fp-btn-possession-home" @if (! $possessionMatch->started_at || $fpPossessionStatus !== 'ONGOING' || $possessionMatch->timer_pause_started_at) disabled @endif>
                🏠 {{ __('Home ball') }} — {{ $homeName }}
            </button>
            <button type="button" class="btn-possession-away" id="fp-btn-possession-away" @if (! $possessionMatch->started_at || $fpPossessionStatus !== 'ONGOING' || $possessionMatch->timer_pause_started_at) disabled @endif>
                ✈️ {{ __('Away ball') }} — {{ $awayName }}
            </button>
            <button type="button" class="btn-possession-neutral" id="fp-btn-possession-neutral" @if (! $possessionMatch->started_at || $fpPossessionStatus !== 'ONGOING' || $possessionMatch->timer_pause_started_at) disabled @endif>
                ⚪ {{ __('Ball out of play') }}
            </button>
        @endif
        @if ($showFullLogLink)
            <a href="{{ route('admin.matches.details', $possessionMatch->id) }}" class="btn btn-outline-secondary btn-sm align-self-center">{{ __('Full log') }}</a>
        @endif
    </div>

    <div class="fp-possession-viz" id="fp-possession-viz" aria-label="{{ __('Possession') }}">
        <div class="fp-possession-viz-head">
            <div class="fp-possession-viz-team">
                @if ($homeAvatarUrl)
                    <img src="{{ $homeAvatarUrl }}" alt="">
                @endif
                <span class="fp-possession-viz-code">{{ $homeCode }}</span>
            </div>
            <div class="fp-possession-viz-team fp-possession-viz-team--away">
                @if ($awayAvatarUrl)
                    <img src="{{ $awayAvatarUrl }}" alt="">
                @endif
                <span class="fp-possession-viz-code">{{ $awayCode }}</span>
            </div>
        </div>
        <div class="fp-possession-viz-title">{{ __('Possession') }}</div>
        <div class="fp-possession-viz-row">
            <div class="fp-poss-pct fp-poss-pct--home">
                <span id="fp-poss-pct-home-inner">
                    @if ($hasSplitPct)
                        <span class="fp-poss-pct-num">{{ $hPct }}</span><span class="fp-poss-pct-sup">%</span>
                    @else
                        <span class="fp-poss-pct-num">—</span>
                    @endif
                </span>
            </div>
            <div class="fp-poss-donut-wrap">
                <div
                    class="fp-poss-donut-ring @if (! $hasSplitPct) fp-poss-donut-ring--empty @endif"
                    id="fp-poss-donut-ring"
                    style="--home-share: {{ $donutShare }};"
                ></div>
                <div class="fp-poss-donut-hole" aria-hidden="true"></div>
                <div class="fp-poss-donut-center">
                    @if ($homeAvatarUrl)
                        <img src="{{ $homeAvatarUrl }}" alt="">
                    @else
                        <span class="fp-poss-donut-ph" title="{{ $homeName }}">{{ mb_strtoupper(mb_substr($homeName, 0, 1)) }}</span>
                    @endif
                    <span class="fp-poss-donut-divider" aria-hidden="true"></span>
                    @if ($awayAvatarUrl)
                        <img src="{{ $awayAvatarUrl }}" alt="">
                    @else
                        <span class="fp-poss-donut-ph" title="{{ $awayName }}">{{ mb_strtoupper(mb_substr($awayName, 0, 1)) }}</span>
                    @endif
                </div>
            </div>
            <div class="fp-poss-pct fp-poss-pct--away">
                <span id="fp-poss-pct-away-inner">
                    @if ($hasSplitPct)
                        <span class="fp-poss-pct-num">{{ $aPct }}</span><span class="fp-poss-pct-sup">%</span>
                    @else
                        <span class="fp-poss-pct-num">—</span>
                    @endif
                </span>
            </div>
        </div>
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
            @if (($pSum['neutral_seconds'] ?? 0) > 0)
                · <span class="text-muted">{{ __('Ball out of play') }}: {{ $fmtPossDur((int) $pSum['neutral_seconds']) }}</span>
            @endif
            @if (($pSum['unknown_seconds'] ?? 0) > 0)
                · <span class="text-muted">{{ __('Before first switch') }}: {{ $fmtPossDur((int) $pSum['unknown_seconds']) }}</span>
            @endif
        </span>
    </div>

    <h6 class="text-secondary mt-3 mb-2">{{ __('Possession log') }}</h6>
    <div class="table-responsive fp-possession-log-wrap">
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
                        <td>{{ $row->club_id === null ? __('Ball out (no possession)') : ($row->club->name ?? '—') }}</td>
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
