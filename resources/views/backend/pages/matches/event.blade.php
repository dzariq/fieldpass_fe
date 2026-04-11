@extends('backend.layouts.master')

@section('title')
Match Events
@endsection

@section('styles')
<style>
    body {
        background: #f5f7fa;
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .page-title-area {
        background: white;
        border-radius: 16px;
        padding: 24px 32px;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .match-info {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 4px;
    }

    .match-score {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #86efac;
        padding: 12px 24px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 12px;
    }

    .match-score span {
        font-size: 1.25rem;
        font-weight: 700;
        color: #166534;
    }

    .main-content-inner {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }

    /* Team Tabs */
    .nav-tabs {
        border: none;
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        background: #f9fafb;
        padding: 4px;
        border-radius: 12px;
    }

    .nav-tabs .nav-item {
        flex: 1;
    }

    .nav-tabs .nav-link {
        background: transparent;
        border: none;
        border-radius: 8px;
        padding: 12px 20px;
        color: #6b7280;
        font-weight: 600;
        font-size: 0.938rem;
        text-align: center;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .nav-tabs .nav-link:hover {
        background: white;
        color: #374151;
    }

    .nav-tabs .nav-link.active {
        background: white;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .no-lineup-message {
        background: #fef3c7;
        border: 1px solid #fde68a;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        color: #92400e;
        font-size: 0.938rem;
        font-weight: 500;
    }

    .player-actions-section {
        background: #f9fafb;
        border-radius: 12px;
        padding: 24px;
        margin-top: 16px;
        border: 1px solid #e5e7eb;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .section-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .section-title {
        color: #111827;
        font-size: 1.125rem;
        font-weight: 700;
        margin: 0;
    }

    /* Recorded Events */
    .recorded-events {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        border: 1px solid #e5e7eb;
    }

    .events-title {
        color: #111827;
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .event-type-section {
        margin-bottom: 16px;
    }

    .event-type-header {
        padding: 10px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .event-type-header.goals {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #065f46;
    }

    .event-type-header.assists {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
    }

    .event-type-header.substitutions {
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        color: #5b21b6;
    }

    .event-type-header.yellow {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .event-type-header.red {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .event-type-header.penalty-missed {
        background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
        color: #9a3412;
    }

    .event-type-header.penalty-saved {
        background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%);
        color: #155e75;
    }

    .event-type-header.own-goal {
        background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
        color: #374151;
    }

    .event-row-display {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 6px;
        border: 1px solid #f3f4f6;
    }

    .event-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .event-minute {
        background: white;
        color: #374151;
        padding: 4px 10px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 0.813rem;
        min-width: 45px;
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    .player-details {
        display: flex;
        flex-direction: column;
    }

    .player-name {
        font-weight: 600;
        color: #111827;
        font-size: 0.875rem;
    }

    .club-name {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 500;
    }

    .btn-delete {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .btn-delete:hover {
        background: #fecaca;
    }

    /* Action Cards */
    .action-player-select {
        background: white;
        border-radius: 10px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #e5e7eb;
    }

    .action-player-select__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .action-player-select__head .action-type-badge {
        margin-bottom: 0;
    }

    .action-player-select__actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
    }

    .btn-fp-undo-section {
        display: none;
        width: 40px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        cursor: pointer;
        font-size: 1.1rem;
        line-height: 1;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        padding: 0;
    }

    .btn-fp-undo-section:hover {
        background: #f3f4f6;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    .action-player-select.fp-action-section--dirty .btn-fp-undo-section {
        display: flex;
    }

    .btn-fp-save-section {
        flex-shrink: 0;
        width: 40px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid #c7d2fe;
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        cursor: pointer;
        font-size: 1.05rem;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .btn-fp-save-section:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.2);
    }

    .action-player-select.fp-action-section--dirty {
        border-color: #f59e0b;
        box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.35), 0 4px 14px rgba(245, 158, 11, 0.12);
        background: linear-gradient(180deg, #fffbeb 0%, #ffffff 48px);
    }

    .action-player-select.fp-action-section--dirty .btn-fp-save-section {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        animation: fp-save-pulse 1.6s ease-in-out infinite;
    }

    @keyframes fp-save-pulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.35); }
        50% { box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12); }
    }

    .action-type-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.813rem;
        margin-bottom: 12px;
    }

    .action-type-badge.goal {
        background: #d1fae5;
        color: #065f46;
    }

    .action-type-badge.assist {
        background: #dbeafe;
        color: #1e40af;
    }

    .action-type-badge.yellow {
        background: #fef3c7;
        color: #92400e;
    }

    .action-type-badge.red {
        background: #fee2e2;
        color: #991b1b;
    }

    .action-type-badge.substitution {
        background: #ede9fe;
        color: #5b21b6;
    }

    .action-type-badge.penalty-missed {
        background: #fed7aa;
        color: #9a3412;
    }

    .action-type-badge.penalty-saved {
        background: #cffafe;
        color: #155e75;
    }

    .action-type-badge.own-goal {
        background: #e5e7eb;
        color: #374151;
    }

    .actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 12px;
        margin-top: 12px;
    }

    .action-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 8px;
        margin-bottom: 8px;
        align-items: end;
    }

    .substitution-row {
        display: grid;
        grid-template-columns: 1.5fr 1.5fr 1fr;
        gap: 8px;
        margin-bottom: 8px;
        align-items: end;
    }

    /* Form Controls */
    .form-control.action-input {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        height: 36px;
        background: white;
        color: #111827;
    }

    .form-control.action-input:focus {
        border-color: #818cf8;
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.1);
        outline: none;
    }

    select.form-control.action-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 8px center;
        background-repeat: no-repeat;
        background-size: 16px;
        padding-right: 32px;
    }

    label {
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #6b7280 !important;
        margin-bottom: 4px !important;
        display: block;
    }

    .minute-input-group {
        position: relative;
    }

    .minute-input-group::after {
        content: '′';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-weight: 600;
        pointer-events: none;
        font-size: 0.875rem;
    }

    .minute-input-group input {
        padding-right: 28px;
    }

    /* Buttons */
    .btn-add-action {
        background: linear-gradient(135deg, #a7f3d0 0%, #6ee7b7 100%);
        border: 1px solid #86efac;
        color: #065f46;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.813rem;
        transition: all 0.2s ease;
        cursor: pointer;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .btn-add-action:hover {
        background: linear-gradient(135deg, #6ee7b7 0%, #34d399 100%);
        transform: translateY(-1px);
    }

    .fp-event-save-mask {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10050;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(2px);
    }

    .fp-event-save-mask.fp-event-save-mask--active {
        display: flex;
    }

    .fp-event-save-mask__panel {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 28px;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.18);
        border: 1px solid #e5e7eb;
        font-weight: 600;
        font-size: 0.938rem;
        color: #111827;
    }

    .fp-event-save-mask__spinner {
        width: 22px;
        height: 22px;
        border: 3px solid #e5e7eb;
        border-top-color: #6366f1;
        border-radius: 50%;
        animation: fp-event-save-spin 0.7s linear infinite;
    }

    @keyframes fp-event-save-spin {
        to { transform: rotate(360deg); }
    }

    .sub-direction-label {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #6b7280 !important;
        margin-bottom: 4px !important;
    }

    .sub-out-label::before {
        content: '↓';
        color: #ef4444;
        font-size: 1rem;
    }

    .sub-in-label::before {
        content: '↑';
        color: #10b981;
        font-size: 1rem;
    }

    .deadline-warning {
        background: #fef3c7;
        border: 1px solid #fde68a;
        padding: 12px 20px;
        border-radius: 10px;
        margin: 16px 0;
        color: #92400e;
        font-weight: 600;
        text-align: center;
        font-size: 0.875rem;
    }

    /* Compact match-day UX helpers */
    details.recorded-events {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px;
        margin: 14px 0;
    }
    details.recorded-events > summary {
        list-style: none;
        cursor: pointer;
        font-weight: 800;
        color: #111827;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        user-select: none;
    }
    details.recorded-events > summary::-webkit-details-marker {
        display: none;
    }
    .fp-summary-right {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-weight: 700;
        font-size: 0.82rem;
    }
    .fp-chevron {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: grid;
        place-items: center;
        background: #f3f4f6;
        color: #374151;
        font-weight: 900;
        line-height: 1;
    }
    details.recorded-events[open] .fp-chevron {
        transform: rotate(180deg);
    }
    .fp-recorded-body {
        margin-top: 10px;
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .nav-tabs {
            flex-direction: column;
        }

        .actions-grid {
            grid-template-columns: 1fr;
        }

        .action-row,
        .substitution-row {
            grid-template-columns: 1fr;
        }

        .page-title-area {
            padding: 12px 14px;
            border-radius: 14px;
            margin-bottom: 14px;
        }

        .main-content-inner {
            padding: 12px;
            border-radius: 14px;
        }

        .page-title {
            font-size: 1.15rem;
        }

        .match-score {
            padding: 8px 14px;
            border-radius: 10px;
        }
        .match-score span {
            font-size: 1.05rem;
        }

        .player-actions-section {
            padding: 14px;
        }
        .action-player-select {
            padding: 12px;
        }
        .action-type-badge {
            padding: 5px 10px;
            font-size: 0.78rem;
            margin-bottom: 10px;
        }
    }

    @media (min-width: 1200px) {
        .actions-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .possession-timer-panel {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
    }

    .possession-timer-panel h5 {
        margin: 0 0 4px;
        font-size: 1rem;
        font-weight: 700;
        color: #1e3a8a;
    }

    .possession-timer-panel .possession-sub {
        color: #475569;
        font-size: 0.8125rem;
        margin-bottom: 12px;
    }

    .match-timer-display {
        font-size: 1.75rem;
        font-weight: 800;
        font-variant-numeric: tabular-nums;
        color: #1d4ed8;
        letter-spacing: 0.02em;
        line-height: 1.2;
    }

    .possession-btn-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 14px;
        align-items: center;
    }

    .possession-btn-row form {
        margin: 0;
    }

    .btn-possession-home,
    .btn-possession-away,
    .btn-possession-neutral {
        min-width: 160px;
        font-weight: 600;
        padding: 10px 16px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
    }

    .btn-possession-home {
        background: #2563eb;
        color: #fff;
    }

    .btn-possession-away {
        background: #7c3aed;
        color: #fff;
    }

    .btn-possession-neutral {
        background: #475569;
        color: #fff;
    }

    .btn-possession-home:disabled,
    .btn-possession-away:disabled,
    .btn-possession-neutral:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    .possession-mini-stats {
        font-size: 0.8125rem;
        color: #334155;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #bfdbfe;
    }
</style>
@endsection

@section('admin-content')

@if(!$match)
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h4 class="page-title">🏆 {{ __('Match Lineup') }}</h4>
        </div>
        <div class="col-sm-4 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    <div style="text-align: center; padding: 40px;">
        <div style="font-size: 48px;">⚽</div>
        <h2 style="color: #111827; font-weight: 700; margin: 16px 0 8px;">{{ __('No Upcoming Match') }}</h2>
        <p style="color: #6b7280;">{{ __('There are currently no scheduled matches available.') }}</p>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="margin-top: 16px;">← {{ __('Back to Dashboard') }}</a>
    </div>
</div>

@else

@php
$now = \Carbon\Carbon::now();
$matchDate = \Carbon\Carbon::parse($match->date);
$submissionDeadline = $matchDate->copy()->subHours(24);
$deadlinePassed = $now->gt($submissionDeadline);

$starterIdsHome = $existingLineupHome ? [
    $existingLineupHome->gk,
    $existingLineupHome->player1,
    $existingLineupHome->player2,
    $existingLineupHome->player3,
    $existingLineupHome->player4,
    $existingLineupHome->player5,
    $existingLineupHome->player6,
    $existingLineupHome->player7,
    $existingLineupHome->player8,
    $existingLineupHome->player9,
    $existingLineupHome->player10,
] : [];

$subIdsHome = $existingLineupHome ? [
    $existingLineupHome->sub1,
    $existingLineupHome->sub2,
    $existingLineupHome->sub3,
    $existingLineupHome->sub4,
    $existingLineupHome->sub5,
    $existingLineupHome->sub6,
    $existingLineupHome->sub7,
    $existingLineupHome->sub8,
    $existingLineupHome->sub9,
] : [];

$starterIdsAway = $existingLineupAway ? [
    $existingLineupAway->gk,
    $existingLineupAway->player1,
    $existingLineupAway->player2,
    $existingLineupAway->player3,
    $existingLineupAway->player4,
    $existingLineupAway->player5,
    $existingLineupAway->player6,
    $existingLineupAway->player7,
    $existingLineupAway->player8,
    $existingLineupAway->player9,
    $existingLineupAway->player10,
] : [];

$subIdsAway = $existingLineupAway ? [
    $existingLineupAway->sub1,
    $existingLineupAway->sub2,
    $existingLineupAway->sub3,
    $existingLineupAway->sub4,
    $existingLineupAway->sub5,
    $existingLineupAway->sub6,
    $existingLineupAway->sub7,
    $existingLineupAway->sub8,
    $existingLineupAway->sub9,
] : [];

// Substitution dropdowns: on-pitch vs bench (replays match_events so subbed-in players appear as "player out").
$subOutIdsHome = $substitutionPlayerOutIdsHome ?? $starterIdsHome;
$subInIdsHome = $substitutionPlayerInIdsHome ?? $subIdsHome;
$subOutIdsAway = $substitutionPlayerOutIdsAway ?? $starterIdsAway;
$subInIdsAway = $substitutionPlayerInIdsAway ?? $subIdsAway;
@endphp

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h4 class="page-title">{{ $match->home_club_name }} vs {{ $match->away_club_name }}</h4>
            <p class="match-info">📅 {{ $matchDate->format('d M Y H:i') }}</p>
            <div class="match-score">
                <span>{{ $match->home_score ?? 0 }} - {{ $match->away_score ?? 0 }}</span>
            </div>
        </div>
        <div class="col-sm-4 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    @include('backend.layouts.partials.messages')

    @if ($deadlinePassed)
    <div class="deadline-warning">
        🚫 {{ __('Submission closed. Deadline was ') . $submissionDeadline->format('d M Y H:i') }}
    </div>
    @endif

    @if (!empty($possessionMatch))
        @include('backend.pages.matches.partials.match-possession-ajax-panel', [
            'possessionMatch' => $possessionMatch,
            'homeClubId' => (int) $match->home_club_id,
            'awayClubId' => (int) $match->away_club_id,
            'homeName' => $match->home_club_name,
            'awayName' => $match->away_club_name,
            'possessionSummary' => $possessionSummary ?? [],
            'canEdit' => true,
            'showFullLogLink' => true,
        ])
    @endif

    <ul class="nav nav-tabs" id="teamTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home-team" role="tab">
                🏠 {{ $match->home_club_name }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="away-tab" data-toggle="tab" href="#away-team" role="tab">
                ✈️ {{ $match->away_club_name }}
            </a>
        </li>
    </ul>
    <div id="fp-recorded-events-wrap">
        @include('backend.pages.matches.partials.recorded-events-block', ['match' => $match, 'matchEvents' => $matchEvents ?? []])
    </div>
    <div class="tab-content">
        <!-- HOME TEAM TAB -->
        <div class="tab-pane fade show active" id="home-team" role="tabpanel">
            @if($existingLineupHome && $playersHome && count($playersHome) > 0)
            <div class="player-actions-section">
                <div class="section-header">
                    <div class="section-icon">📊</div>
                    <h4 class="section-title">{{ __('Match Statistics') }} - {{ $match->home_club_name }}</h4>
                </div>

                <!-- Display Existing Events -->
              

                <form action="{{ route('admin.match.event_save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="match_id" value="{{ $match->id }}">
                    <input type="hidden" name="lineup_id" value="{{ $existingLineupHome->id ?? '' }}">
                    <input type="hidden" name="club_id" value="{{ $match->home_club_id }}">

                    <div class="actions-grid">
                        <!-- Goals HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge goal">⚽ {{ __('Goals') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="goals-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="goals[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->jersey_number }} {{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="goals[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="goals">+ {{ __('Add Goal') }}</button>
                        </div>

                        <!-- Assists HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge assist">🎯 {{ __('Assists') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="assists-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="assists[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="assists[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="assists">+ {{ __('Add Assist') }}</button>
                        </div>

                        <!-- Substitutions HOME -->
                        <div class="action-player-select" style="grid-column: 1 / -1;">
                            <div class="action-player-select__head">
                                <div class="action-type-badge substitution">🔄 {{ __('Substitutions') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="substitutions-container-home">
                                <div class="substitution-row">
                                    <div>
                                        <label class="sub-direction-label sub-out-label">{{ __('Player Out') }}</label>
                                        <select name="substitutions[0][player_out_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            @if (in_array($player->id, $subOutIdsHome))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="sub-direction-label sub-in-label">{{ __('Player In') }}</label>
                                        <select name="substitutions[0][player_in_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            @if (in_array($player->id, $subInIdsHome))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="substitutions[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-substitution="1">+ {{ __('Add Substitution') }}</button>
                        </div>

                        <!-- Yellow Cards HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge yellow">🟨 {{ __('Yellow Cards') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="yellow_cards-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="yellow_cards[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="yellow_cards[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="yellow_cards">+ {{ __('Add Yellow Card') }}</button>
                        </div>

                        <!-- Red Cards HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge red">🟥 {{ __('Red Cards') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="red_cards-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="red_cards[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="red_cards[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="red_cards">+ {{ __('Add Red Card') }}</button>
                        </div>

                        <!-- Penalty Missed HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge penalty-missed">❌ {{ __('Penalty Missed') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="penalty_missed-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="penalty_missed[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="penalty_missed[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="penalty_missed">+ {{ __('Add Penalty Missed') }}</button>
                        </div>

                        <!-- Penalty Saved HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge penalty-saved">🧤 {{ __('Penalty Saved') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="penalty_saved-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Goalkeeper') }}</label>
                                        <select name="penalty_saved[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Goalkeeper') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="penalty_saved[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="penalty_saved">+ {{ __('Add Penalty Saved') }}</button>
                        </div>

                        <!-- Own Goals HOME -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge own-goal">⚠️ {{ __('Own Goal') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="own_goals-container-home">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="own_goals[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="own_goals[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="home" data-fp-row="own_goals">+ {{ __('Add Own Goal') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="no-lineup-message">
                ⚠️ {{ __('No lineup submitted for') }} {{ $match->home_club_name }}
            </div>
            @endif
        </div>

        <!-- AWAY TEAM TAB -->
        <div class="tab-pane fade" id="away-team" role="tabpanel">
            @if($existingLineupAway && $playersAway && count($playersAway) > 0)
            <div class="player-actions-section">
                <div class="section-header">
                    <div class="section-icon">📊</div>
                    <h4 class="section-title">{{ __('Match Statistics') }} - {{ $match->away_club_name }}</h4>
                </div>

                <form action="{{ route('admin.match.event_save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="match_id" value="{{ $match->id }}">
                    <input type="hidden" name="lineup_id" value="{{ $existingLineupAway->id ?? '' }}">
                    <input type="hidden" name="club_id" value="{{ $match->away_club_id }}">

                    <div class="actions-grid">
                        <!-- Goals AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge goal">⚽ {{ __('Goals') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="goals-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="goals[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="goals[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="goals">+ {{ __('Add Goal') }}</button>
                        </div>

                        <!-- Assists AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge assist">🎯 {{ __('Assists') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="assists-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="assists[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="assists[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="assists">+ {{ __('Add Assist') }}</button>
                        </div>

                        <!-- Substitutions AWAY -->
                        <div class="action-player-select" style="grid-column: 1 / -1;">
                            <div class="action-player-select__head">
                                <div class="action-type-badge substitution">🔄 {{ __('Substitutions') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="substitutions-container-away">
                                <div class="substitution-row">
                                    <div>
                                        <label class="sub-direction-label sub-out-label">{{ __('Player Out') }}</label>
                                        <select name="substitutions[0][player_out_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            @if (in_array($player->id, $subOutIdsAway))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="sub-direction-label sub-in-label">{{ __('Player In') }}</label>
                                        <select name="substitutions[0][player_in_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            @if (in_array($player->id, $subInIdsAway))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="substitutions[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-substitution="1">+ {{ __('Add Substitution') }}</button>
                        </div>

                        <!-- Yellow Cards AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge yellow">🟨 {{ __('Yellow Cards') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="yellow_cards-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="yellow_cards[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="yellow_cards[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="yellow_cards">+ {{ __('Add Yellow Card') }}</button>
                        </div>

                        <!-- Red Cards AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge red">🟥 {{ __('Red Cards') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="red_cards-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="red_cards[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="red_cards[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="red_cards">+ {{ __('Add Red Card') }}</button>
                        </div>

                        <!-- Penalty Missed AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge penalty-missed">❌ {{ __('Penalty Missed') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="penalty_missed-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="penalty_missed[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="penalty_missed[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="penalty_missed">+ {{ __('Add Penalty Missed') }}</button>
                        </div>

                        <!-- Penalty Saved AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge penalty-saved">🧤 {{ __('Penalty Saved') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="penalty_saved-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Goalkeeper') }}</label>
                                        <select name="penalty_saved[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Goalkeeper') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="penalty_saved[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="penalty_saved">+ {{ __('Add Penalty Saved') }}</button>
                        </div>

                        <!-- Own Goals AWAY -->
                        <div class="action-player-select">
                            <div class="action-player-select__head">
                                <div class="action-type-badge own-goal">⚠️ {{ __('Own Goal') }}</div>
                                <div class="action-player-select__actions">
                                    <button type="button" class="btn-fp-undo-section" title="{{ __('Discard unsaved changes in this section') }}" aria-label="{{ __('Undo') }}">↩</button>
                                    <button type="button" class="btn-fp-save-section" title="{{ __('Save entries in this section') }}" aria-label="{{ __('Save') }}">💾</button>
                                </div>
                            </div>
                            <div id="own_goals-container-away">
                                <div class="action-row">
                                    <div>
                                        <label>{{ __('Player') }}</label>
                                        <select name="own_goals[0][player_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="own_goals[0][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" data-fp-team="away" data-fp-row="own_goals">+ {{ __('Add Own Goal') }}</button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="no-lineup-message">
                ⚠️ {{ __('No lineup submitted for') }} {{ $match->away_club_name }}
            </div>
            @endif
        </div>
    </div>

    <div id="fp-event-save-mask" class="fp-event-save-mask" role="status" aria-live="polite" aria-hidden="true">
        <div class="fp-event-save-mask__panel">
            <span class="fp-event-save-mask__spinner" aria-hidden="true"></span>
            <span class="fp-event-save-mask__text">{{ __('Saving…') }}</span>
        </div>
    </div>
</div>

@endif

@endsection

@if($match)
@section('scripts')
<script>
(function () {
    function fpMinuteFromPlayingSeconds(s) {
        if (s == null || s < 0) return null;
        var m = Math.floor(s / 60) + 1;
        if (m < 1) m = 1;
        if (m > 120) m = 120;
        return String(m);
    }
    function fpBindMatchMinuteInputs() {
        document.querySelectorAll('input.js-match-minute-sync').forEach(function (el) {
            if (el.dataset.fpMinuteListen) return;
            el.dataset.fpMinuteListen = '1';
            el.addEventListener('input', function () {
                el.dataset.fpMinuteUserEdited = '1';
            });
            el.addEventListener('change', function () {
                el.dataset.fpMinuteUserEdited = '1';
            });
        });
    }
    function fpSyncMatchMinuteInputs(playingSeconds) {
        fpBindMatchMinuteInputs();
        var m = fpMinuteFromPlayingSeconds(playingSeconds);
        if (m === null) return;
        document.querySelectorAll('input.js-match-minute-sync').forEach(function (el) {
            if (el.dataset.fpMinuteUserEdited === '1') return;
            if (document.activeElement === el) return;
            el.value = m;
        });
    }
    window.addEventListener('fp-match-playing-seconds', function (e) {
        var sec = e.detail ? e.detail.seconds : null;
        window.__fpLastPlayingSecondsForMinutes = sec;
        fpSyncMatchMinuteInputs(sec);
    });
})();

    @if (!empty($possessionMatch))
        @include('backend.pages.matches.partials.match-possession-ajax-script')
    @endif

    let actionCounters = {
        goals: 1,
        assists: 1,
        yellow_cards: 1,
        red_cards: 1,
        substitutions: 1,
        penalty_missed: 1,
        penalty_saved: 1,
        own_goals: 1
    };

    var FP_ALL_PLAYERS_HOME = @json($playersHome->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name])->values());
    var FP_ALL_PLAYERS_AWAY = @json($playersAway->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name])->values());

    function fpFilterPlayersByIds(allPlayers, ids) {
        if (!allPlayers || !ids || !ids.length) return [];
        var idSet = {};
        ids.forEach(function (id) { idSet[Number(id)] = true; });
        return allPlayers.filter(function (p) { return idSet[Number(p.id)]; });
    }

    var fpSubOutPlayersHome = fpFilterPlayersByIds(FP_ALL_PLAYERS_HOME, @json(array_values($subOutIdsHome)));
    var fpSubInPlayersHome = fpFilterPlayersByIds(FP_ALL_PLAYERS_HOME, @json(array_values($subInIdsHome)));
    var fpSubOutPlayersAway = fpFilterPlayersByIds(FP_ALL_PLAYERS_AWAY, @json(array_values($subOutIdsAway)));
    var fpSubInPlayersAway = fpFilterPlayersByIds(FP_ALL_PLAYERS_AWAY, @json(array_values($subInIdsAway)));

    var FP_SELECT_PLAYER_PLACEHOLDER = @json(__('Select Player'));

    function fpFillSubstitutionPlayerSelect(sel, players) {
        if (!sel) return;
        var cur = String(sel.value || '');
        sel.innerHTML = '';
        var opt0 = document.createElement('option');
        opt0.value = '';
        opt0.textContent = FP_SELECT_PLAYER_PLACEHOLDER;
        sel.appendChild(opt0);
        (players || []).forEach(function (p) {
            var o = document.createElement('option');
            o.value = String(p.id);
            o.textContent = p.name;
            sel.appendChild(o);
        });
        if (cur && Array.prototype.some.call(sel.options, function (o) { return o.value === cur; })) {
            sel.value = cur;
        }
    }

    function fpRefreshAllSubstitutionSelectOptions() {
        [['home', fpSubOutPlayersHome, fpSubInPlayersHome], ['away', fpSubOutPlayersAway, fpSubInPlayersAway]].forEach(function (cfg) {
            var team = cfg[0];
            var outList = cfg[1];
            var inList = cfg[2];
            var container = document.getElementById('substitutions-container-' + team);
            if (!container) return;
            container.querySelectorAll('.substitution-row').forEach(function (row) {
                var outSel = row.querySelector('select[name*="[player_out_id]"]');
                var inSel = row.querySelector('select[name*="[player_in_id]"]');
                fpFillSubstitutionPlayerSelect(outSel, outList);
                fpFillSubstitutionPlayerSelect(inSel, inList);
            });
        });
    }

    function fpApplySubstitutionListsFromApi(data) {
        if (!data || !data.substitution_lists) return;
        var h = data.substitution_lists.home;
        var a = data.substitution_lists.away;
        if (h && Array.isArray(h.out) && Array.isArray(h.in)) {
            fpSubOutPlayersHome = fpFilterPlayersByIds(FP_ALL_PLAYERS_HOME, h.out);
            fpSubInPlayersHome = fpFilterPlayersByIds(FP_ALL_PLAYERS_HOME, h.in);
        }
        if (a && Array.isArray(a.out) && Array.isArray(a.in)) {
            fpSubOutPlayersAway = fpFilterPlayersByIds(FP_ALL_PLAYERS_AWAY, a.out);
            fpSubInPlayersAway = fpFilterPlayersByIds(FP_ALL_PLAYERS_AWAY, a.in);
        }
        fpRefreshAllSubstitutionSelectOptions();
    }

    function fpUndoActionKeyFromContainerId(containerId) {
        var m = String(containerId || '').match(/^([a-z_]+)-container-(home|away)$/);
        return m ? m[1] : null;
    }

    function fpRecalculateActionCounter(actionKey) {
        if (!actionKey || !Object.prototype.hasOwnProperty.call(actionCounters, actionKey)) return;
        var esc = actionKey.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        var re = new RegExp('^' + esc + '\\[(\\d+)\\]');
        var maxI = -1;
        document.querySelectorAll('.player-actions-section select[name]').forEach(function (sel) {
            var mm = sel.name.match(re);
            if (mm) maxI = Math.max(maxI, parseInt(mm[1], 10));
        });
        if (maxI >= 0) {
            actionCounters[actionKey] = maxI + 1;
        }
    }

    function fpEnsureSectionUndoBaseline(section) {
        if (!section || section._fpUndoBaseline) return;
        var inner = section.querySelector('[id$="-container-home"], [id$="-container-away"]');
        if (!inner || !inner.id) return;
        var key = fpUndoActionKeyFromContainerId(inner.id);
        if (!key) return;
        section._fpUndoBaseline = {
            containerId: inner.id,
            html: inner.innerHTML,
            counterKey: key
        };
    }

    function fpApplySectionUndo(btn) {
        var section = btn.closest('.action-player-select');
        if (!section || !section._fpUndoBaseline) return;
        var b = section._fpUndoBaseline;
        var inner = document.getElementById(b.containerId);
        if (!inner) return;
        inner.innerHTML = b.html;
        fpRecalculateActionCounter(b.counterKey);
        delete section._fpUndoBaseline;
        section.classList.remove('fp-action-section--dirty');
        if (typeof window.dispatchEvent === 'function' && window.__fpLastPlayingSecondsForMinutes !== undefined) {
            window.dispatchEvent(new CustomEvent('fp-match-playing-seconds', { detail: { seconds: window.__fpLastPlayingSecondsForMinutes } }));
        }
    }

    var FP_ADD_ACTION_ROW_MSG = @json(__('Select a player and a valid minute (1–120) for the current row before adding another.'));
    var FP_ADD_SUB_ROW_MSG = @json(__('Select both players and a valid minute (1–120) before adding another substitution.'));
    var FP_ADD_WHILE_EMPTY_ROW_MSG = @json(__('Use the current row first: select a player and a minute before adding another row.'));
    var FP_ADD_WHILE_EMPTY_SUB_MSG = @json(__('Use the current substitution row first: both players and a minute before adding another.'));

    function fpMinuteFieldState(row) {
        var minuteInput = row.querySelector('input[type="number"].js-match-minute-sync') || row.querySelector('input[type="number"]');
        var raw = minuteInput ? String(minuteInput.value).trim() : '';
        if (raw === '') {
            return { touched: false, valid: false };
        }
        var n = parseInt(raw, 10);
        return { touched: true, valid: Number.isFinite(n) && n >= 1 && n <= 120 };
    }

    function fpActionRowIsIncomplete(row) {
        if (row.classList.contains('substitution-row')) {
            var outSel = row.querySelector('select[name*="[player_out_id]"]');
            var inSel = row.querySelector('select[name*="[player_in_id]"]');
            var outSet = outSel && String(outSel.value).trim() !== '';
            var inSet = inSel && String(inSel.value).trim() !== '';
            var mv = fpMinuteFieldState(row);
            var any = outSet || inSet || mv.touched;
            if (!any) return false;
            var outId = outSet ? parseInt(String(outSel.value).trim(), 10) : NaN;
            var inId = inSet ? parseInt(String(inSel.value).trim(), 10) : NaN;
            var complete = Number.isFinite(outId) && outId >= 1 && Number.isFinite(inId) && inId >= 1 && mv.valid;
            return !complete;
        }
        var pSel = row.querySelector('select[name*="[player_id]"]');
        var playerSet = pSel && String(pSel.value).trim() !== '';
        var mv = fpMinuteFieldState(row);
        var any = playerSet || mv.touched;
        if (!any) return false;
        var pid = playerSet ? parseInt(String(pSel.value).trim(), 10) : NaN;
        var complete = Number.isFinite(pid) && pid >= 1 && mv.valid;
        return !complete;
    }

    function fpContainerHasIncompleteRow(container) {
        if (!container) return false;
        var rows = container.querySelectorAll('.action-row, .substitution-row');
        for (var i = 0; i < rows.length; i++) {
            if (fpActionRowIsIncomplete(rows[i])) return true;
        }
        return false;
    }

    /** True when player(s) and minute are all unset (cannot append another blank row). */
    function fpActionRowIsFullyEmpty(row) {
        var mv = fpMinuteFieldState(row);
        var minuteEmpty = !mv.touched;
        if (row.classList.contains('substitution-row')) {
            var outSel = row.querySelector('select[name*="[player_out_id]"]');
            var inSel = row.querySelector('select[name*="[player_in_id]"]');
            var outEmpty = !outSel || String(outSel.value).trim() === '';
            var inEmpty = !inSel || String(inSel.value).trim() === '';
            return outEmpty && inEmpty && minuteEmpty;
        }
        var pSel = row.querySelector('select[name*="[player_id]"]');
        var playerEmpty = !pSel || String(pSel.value).trim() === '';
        return playerEmpty && minuteEmpty;
    }

    function fpContainerHasFullyEmptyRow(container) {
        if (!container) return false;
        var rows = container.querySelectorAll('.action-row, .substitution-row');
        for (var j = 0; j < rows.length; j++) {
            if (fpActionRowIsFullyEmpty(rows[j])) return true;
        }
        return false;
    }

    function addActionRow(actionType, team) {
        const container = document.getElementById(`${actionType}-container-${team}`);
        var secAdd = container ? container.closest('.action-player-select') : null;
        fpEnsureSectionUndoBaseline(secAdd);
        if (fpContainerHasIncompleteRow(container)) {
            window.alert(FP_ADD_ACTION_ROW_MSG);
            return;
        }
        if (fpContainerHasFullyEmptyRow(container)) {
            window.alert(FP_ADD_WHILE_EMPTY_ROW_MSG);
            return;
        }
        const index = actionCounters[actionType];
        const players = team === 'home' ? @json($playersHome ?? []) : @json($playersAway ?? []);

        let playerOptions = '<option value="">Select Player</option>';
        players.forEach(player => {
            playerOptions += `<option value="${player.id}">${player.name}</option>`;
        });

        const rowHtml = `
            <div class="action-row">
                <div>
                    <label>Player</label>
                    <select name="${actionType}[${index}][player_id]" class="form-control action-input">
                        ${playerOptions}
                    </select>
                </div>
                <div class="minute-input-group">
                    <label>Minute</label>
                    <input type="number" name="${actionType}[${index}][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        actionCounters[actionType]++;
        if (typeof window.dispatchEvent === 'function' && window.__fpLastPlayingSecondsForMinutes !== undefined) {
            window.dispatchEvent(new CustomEvent('fp-match-playing-seconds', { detail: { seconds: window.__fpLastPlayingSecondsForMinutes } }));
        }
    }

    function addSubstitutionRow(team) {
        const container = document.getElementById(`substitutions-container-${team}`);
        var secSub = container ? container.closest('.action-player-select') : null;
        fpEnsureSectionUndoBaseline(secSub);
        if (fpContainerHasIncompleteRow(container)) {
            window.alert(FP_ADD_SUB_ROW_MSG);
            return;
        }
        if (fpContainerHasFullyEmptyRow(container)) {
            window.alert(FP_ADD_WHILE_EMPTY_SUB_MSG);
            return;
        }
        const index = actionCounters.substitutions;
        const starterPlayers = team === 'home' ? fpSubOutPlayersHome : fpSubOutPlayersAway;
        const subPlayers = team === 'home' ? fpSubInPlayersHome : fpSubInPlayersAway;

        let playerOutOptions = '<option value="">' + FP_SELECT_PLAYER_PLACEHOLDER + '</option>';
        starterPlayers.forEach(player => {
            playerOutOptions += `<option value="${player.id}">${player.name}</option>`;
        });

        let playerInOptions = '<option value="">' + FP_SELECT_PLAYER_PLACEHOLDER + '</option>';
        subPlayers.forEach(player => {
            playerInOptions += `<option value="${player.id}">${player.name}</option>`;
        });

        const rowHtml = `
            <div class="substitution-row">
                <div>
                    <label class="sub-direction-label sub-out-label">Player Out</label>
                    <select name="substitutions[${index}][player_out_id]" class="form-control action-input">
                        ${playerOutOptions}
                    </select>
                </div>
                <div>
                    <label class="sub-direction-label sub-in-label">Player In</label>
                    <select name="substitutions[${index}][player_in_id]" class="form-control action-input">
                        ${playerInOptions}
                    </select>
                </div>
                <div class="minute-input-group">
                    <label>Minute</label>
                    <input type="number" name="substitutions[${index}][minute]" class="form-control action-input js-match-minute-sync" placeholder="45" min="1" max="120" title="{{ __('Follows live match clock; change anytime to override.') }}">
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        actionCounters.substitutions++;
        if (typeof window.dispatchEvent === 'function' && window.__fpLastPlayingSecondsForMinutes !== undefined) {
            window.dispatchEvent(new CustomEvent('fp-match-playing-seconds', { detail: { seconds: window.__fpLastPlayingSecondsForMinutes } }));
        }
    }

    /** If the page is HTTPS but Laravel generated http:// URLs (APP_URL), avoid mixed-content blocked fetch. */
    function fpEnsureHttpsUrl(url) {
        if (!url || typeof url !== 'string') {
            return url;
        }
        if (typeof window === 'undefined' || window.location.protocol !== 'https:') {
            return url;
        }
        try {
            var u = new URL(url, window.location.href);
            if (u.protocol === 'http:' && u.hostname === window.location.hostname) {
                u.protocol = 'https:';
                return u.href;
            }
        } catch (e) { /* ignore */ }
        return url;
    }

    var FP_EVENT_SAVE_URL = fpEnsureHttpsUrl(@json(route('admin.match.event_save')));
    var FP_EVENT_DELETE_URL = fpEnsureHttpsUrl(@json(route('admin.match.deleteEvent')));
    var FP_FORM_PREFIX_TO_ACTION = {
        goals: 'goal',
        assists: 'assist',
        yellow_cards: 'yellow_card',
        red_cards: 'red_card',
        penalty_missed: 'penalty_missed',
        penalty_saved: 'penalty_saved',
        own_goals: 'own_goal',
        substitutions: 'substitution'
    };

    function fpGetCsrfToken() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function fpBumpEventSaveMask(delta) {
        window.__fpActiveEventSaves = Math.max(0, (window.__fpActiveEventSaves || 0) + delta);
        var el = document.getElementById('fp-event-save-mask');
        if (!el) return;
        if (window.__fpActiveEventSaves > 0) {
            el.classList.add('fp-event-save-mask--active');
            el.setAttribute('aria-hidden', 'false');
        } else {
            el.classList.remove('fp-event-save-mask--active');
            el.setAttribute('aria-hidden', 'true');
        }
    }

    function fpApplyMatchSnapshot(data) {
        if (!data) return;
        var scoreEl = document.querySelector('.match-score span');
        if (scoreEl) scoreEl.textContent = String(data.home_score) + ' - ' + String(data.away_score);
        var titleEl = document.querySelector('.page-title-area .page-title');
        if (titleEl && data.page_title) titleEl.textContent = data.page_title;
        var infoEl = document.querySelector('.page-title-area .match-info');
        if (infoEl && data.match_info) infoEl.textContent = data.match_info;
        var wrap = document.getElementById('fp-recorded-events-wrap');
        if (wrap && data.recorded_events_html !== undefined) wrap.innerHTML = data.recorded_events_html;
        fpApplySubstitutionListsFromApi(data);
    }

    function fpIsRecordedEventDeleteForm(form) {
        if (!form || form.tagName !== 'FORM' || String(form.method).toLowerCase() !== 'post') return false;
        try {
            return new URL(form.action, window.location.href).pathname === new URL(FP_EVENT_DELETE_URL, window.location.href).pathname;
        } catch (e) {
            return false;
        }
    }

    (function () {
        var wrap = document.getElementById('fp-recorded-events-wrap');
        if (!wrap) return;
        wrap.addEventListener('submit', function (e) {
            var form = e.target;
            if (!fpIsRecordedEventDeleteForm(form)) return;
            e.preventDefault();
            fpBumpEventSaveMask(1);
            var fd = new FormData(form);
            fetch(FP_EVENT_DELETE_URL, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': fpGetCsrfToken()
                },
                credentials: 'same-origin',
                body: fd
            })
                .then(function (r) {
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') === -1) {
                        return r.text().then(function () {
                            throw Object.assign(new Error('Unexpected response from server.'), { fpAlerted: true });
                        });
                    }
                    return r.json().then(function (body) {
                        return { ok: r.ok, status: r.status, body: body };
                    });
                })
                .then(function (res) {
                    if (!res.ok || !res.body.success) {
                        var msg = (res.body && res.body.message) ? res.body.message : 'Delete failed';
                        window.alert(msg);
                        throw Object.assign(new Error(msg), { fpAlerted: true });
                    }
                    fpApplyMatchSnapshot(res.body);
                })
                .catch(function (err) {
                    if (!err || !err.fpAlerted) {
                        window.alert((err && err.message) ? err.message : 'Network error');
                    }
                })
                .finally(function () {
                    fpBumpEventSaveMask(-1);
                });
        });
    })();

    function fpResetRowAfterSave(row) {
        row.querySelectorAll('select').forEach(function (s) { s.selectedIndex = 0; });
        var minInput = row.querySelector('input[type="number"]');
        if (minInput) {
            minInput.value = '';
            delete minInput.dataset.fpMinuteUserEdited;
        }
        if (typeof window.dispatchEvent === 'function' && window.__fpLastPlayingSecondsForMinutes !== undefined) {
            window.dispatchEvent(new CustomEvent('fp-match-playing-seconds', { detail: { seconds: window.__fpLastPlayingSecondsForMinutes } }));
        }
    }

    function fpGetFormPrefixFromRow(row) {
        var sel = row.querySelector('select[name]');
        if (!sel || !sel.name) return null;
        var m = sel.name.match(/^([a-z_]+)\[\d+\]\[/);
        return m ? m[1] : null;
    }

    var FP_SECTION_NOTHING_TO_SAVE_MSG = @json(__('Nothing to save in this section. Select a player and a valid minute first.'));

    function fpRowMinuteState(row) {
        var minuteInput = row.querySelector('input[type="number"].js-match-minute-sync') || row.querySelector('input[type="number"]');
        var raw = minuteInput ? minuteInput.value : '';
        var empty = raw === '' || raw === null;
        var num = empty ? NaN : parseInt(raw, 10);
        var valid = !isNaN(num) && num >= 1 && num <= 120;
        return { minuteInput: minuteInput, empty: empty, num: num, valid: valid };
    }

    function fpBuildPayloadForRow(row, form) {
        if (!row || !form) return null;
        var prefix = fpGetFormPrefixFromRow(row);
        if (!prefix || !FP_FORM_PREFIX_TO_ACTION[prefix]) return null;
        var action = FP_FORM_PREFIX_TO_ACTION[prefix];
        var matchIdInput = form.querySelector('input[name="match_id"]');
        var clubIdInput = form.querySelector('input[name="club_id"]');
        var lineupInput = form.querySelector('input[name="lineup_id"]');
        if (!matchIdInput || !clubIdInput) return null;
        var outSel = row.querySelector('select[name*="[player_out_id]"]');
        var inSel = row.querySelector('select[name*="[player_in_id]"]');
        var pSel = row.querySelector('select[name*="[player_id]"]');
        var outId = outSel && String(outSel.value).trim() !== '' ? parseInt(String(outSel.value).trim(), 10) : NaN;
        var inId = inSel && String(inSel.value).trim() !== '' ? parseInt(String(inSel.value).trim(), 10) : NaN;
        var playerIdParsed = pSel && String(pSel.value).trim() !== '' ? parseInt(String(pSel.value).trim(), 10) : NaN;
        var subReady = action === 'substitution' && Number.isFinite(outId) && outId >= 1 && Number.isFinite(inId) && inId >= 1;
        var playerReady = action !== 'substitution' && Number.isFinite(playerIdParsed) && playerIdParsed >= 1;
        var ms = fpRowMinuteState(row);
        if (!ms.valid || ms.empty) return null;
        if (action === 'substitution') {
            if (!subReady) return null;
        } else {
            if (!playerReady || !pSel) return null;
        }
        var payload = {
            match_id: parseInt(matchIdInput.value, 10),
            club_id: parseInt(clubIdInput.value, 10),
            action: action,
            minute: ms.num
        };
        if (lineupInput && lineupInput.value) payload.lineup_id = lineupInput.value;
        if (action === 'substitution') {
            payload.player_out_id = outId;
            payload.player_in_id = inId;
        } else {
            payload.player_id = playerIdParsed;
        }
        return payload;
    }

    function fpPerformRowSave(row, form, payload) {
        return new Promise(function (resolve, reject) {
            if (row.dataset.fpSaving === '1') {
                resolve({ skipped: true });
                return;
            }
            row.dataset.fpSaving = '1';
            fpBumpEventSaveMask(1);
            fetch(fpEnsureHttpsUrl(FP_EVENT_SAVE_URL), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': fpGetCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            })
                .then(function (r) {
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') === -1) {
                        return r.text().then(function () {
                            throw Object.assign(new Error('Unexpected response from server.'), { fpAlerted: true });
                        });
                    }
                    return r.json().then(function (body) {
                        return { ok: r.ok, status: r.status, body: body };
                    });
                })
                .then(function (res) {
                    if (!res.ok || !res.body.success) {
                        var msg = (res.body && res.body.message) ? res.body.message : 'Save failed';
                        if (res.body && res.body.errors && typeof res.body.errors === 'object') {
                            var keys = Object.keys(res.body.errors);
                            if (keys.length && res.body.errors[keys[0]] && res.body.errors[keys[0]][0]) {
                                msg = res.body.errors[keys[0]][0];
                            }
                        }
                        window.alert(msg);
                        throw Object.assign(new Error(msg), { fpAlerted: true });
                    }
                    fpApplyMatchSnapshot(res.body);
                    fpResetRowAfterSave(row);
                    resolve({ ok: true });
                })
                .catch(function (err) {
                    if (!err || !err.fpAlerted) {
                        window.alert((err && err.message) ? err.message : 'Network error');
                    }
                    reject(err);
                })
                .finally(function () {
                    fpBumpEventSaveMask(-1);
                    delete row.dataset.fpSaving;
                });
        });
    }

    function fpSaveReadyRowsSequentially(rows, form, index, onAllDone) {
        if (index >= rows.length) {
            if (typeof onAllDone === 'function') onAllDone();
            return;
        }
        var row = rows[index];
        var payload = fpBuildPayloadForRow(row, form);
        if (!payload) {
            fpSaveReadyRowsSequentially(rows, form, index + 1, onAllDone);
            return;
        }
        fpPerformRowSave(row, form, payload).then(function (res) {
            if (res && res.ok) {
                fpSaveReadyRowsSequentially(rows, form, index + 1, onAllDone);
            }
        }).catch(function () {});
    }

    function fpManualSaveActionSection(btn) {
        var section = btn.closest('.action-player-select');
        if (!section) return;
        var form = section.closest('form');
        if (!form || !form.closest('.player-actions-section')) return;
        var inner = section.querySelector('[id$="-container-home"], [id$="-container-away"]');
        if (!inner) return;
        var rows = inner.querySelectorAll('.action-row, .substitution-row');
        var isSubSection = inner.id.indexOf('substitutions-container') === 0;
        var incMsg = isSubSection ? FP_ADD_SUB_ROW_MSG : FP_ADD_ACTION_ROW_MSG;
        var i;
        for (i = 0; i < rows.length; i++) {
            if (fpActionRowIsIncomplete(rows[i])) {
                window.alert(incMsg);
                return;
            }
        }
        var ready = [];
        for (i = 0; i < rows.length; i++) {
            if (fpBuildPayloadForRow(rows[i], form)) ready.push(rows[i]);
        }
        if (ready.length === 0) {
            window.alert(FP_SECTION_NOTHING_TO_SAVE_MSG);
            var allEmpty = true;
            for (i = 0; i < rows.length; i++) {
                if (!fpActionRowIsFullyEmpty(rows[i])) {
                    allEmpty = false;
                    break;
                }
            }
            if (allEmpty) {
                delete section._fpUndoBaseline;
                section.classList.remove('fp-action-section--dirty');
            }
            return;
        }
        fpSaveReadyRowsSequentially(ready, form, 0, function () {
            delete section._fpUndoBaseline;
            section.classList.remove('fp-action-section--dirty');
        });
    }

    function fpMarkActionSectionDirty(ev) {
        var t = ev.target;
        if (!t || !t.closest('.player-actions-section')) return;
        if (t.tagName !== 'SELECT' && !(t.matches && t.matches('input[type="number"].js-match-minute-sync'))) return;
        var section = t.closest('.action-player-select');
        if (section) section.classList.add('fp-action-section--dirty');
    }

    document.addEventListener('change', fpMarkActionSectionDirty, false);
    document.addEventListener('input', fpMarkActionSectionDirty, false);

    document.addEventListener('focusin', function (ev) {
        var t = ev.target;
        if (!t || !t.closest('.player-actions-section')) return;
        if (t.tagName !== 'SELECT' && !(t.matches && t.matches('input[type="number"].js-match-minute-sync'))) return;
        var secF = t.closest('.action-player-select');
        fpEnsureSectionUndoBaseline(secF);
    }, true);

    document.addEventListener('click', function (e) {
        var sec = e.target.closest('.player-actions-section');
        if (!sec) return;
        var addBtn = e.target.closest('.btn-add-action');
        if (addBtn && sec.contains(addBtn)) {
            if (addBtn.getAttribute('data-fp-substitution') === '1') {
                var tsub = addBtn.getAttribute('data-fp-team');
                if (tsub) addSubstitutionRow(tsub);
            } else {
                var t = addBtn.getAttribute('data-fp-team');
                var r = addBtn.getAttribute('data-fp-row');
                if (t && r) addActionRow(r, t);
            }
            return;
        }
        var undoBtn = e.target.closest('.btn-fp-undo-section');
        if (undoBtn && sec.contains(undoBtn)) {
            e.preventDefault();
            fpApplySectionUndo(undoBtn);
            return;
        }
        var saveBtn = e.target.closest('.btn-fp-save-section');
        if (saveBtn && sec.contains(saveBtn)) {
            e.preventDefault();
            fpManualSaveActionSection(saveBtn);
        }
    }, false);

    document.querySelectorAll('.player-actions-section form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    });
</script>
@endsection
@endif