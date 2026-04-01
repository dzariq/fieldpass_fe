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
        grid-template-columns: 2fr 1fr 36px;
        gap: 8px;
        margin-bottom: 8px;
        align-items: end;
    }

    .substitution-row {
        display: grid;
        grid-template-columns: 1.5fr 1.5fr 1fr 36px;
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

    .btn-remove-action {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #991b1b;
        padding: 8px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        cursor: pointer;
        height: 36px;
        width: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-remove-action:hover {
        background: #fecaca;
    }

    .btn-save-actions {
        background: linear-gradient(135deg, #818cf8 0%, #6366f1 100%);
        border: none;
        color: white;
        padding: 12px 32px;
        border-radius: 10px;
        font-size: 0.938rem;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(99, 102, 241, 0.25);
        margin-top: 16px;
        border: 1px solid #6366f1;
    }

    .btn-save-actions:hover {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
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

        .btn-remove-action {
            width: 100%;
            margin-top: 4px;
        }

        .page-title-area {
            padding: 16px 20px;
        }

        .main-content-inner {
            padding: 16px;
        }
    }

    @media (min-width: 1200px) {
        .actions-grid {
            grid-template-columns: repeat(3, 1fr);
        }
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
] : [];
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
    @if ($deadlinePassed)
    <div class="deadline-warning">
        🚫 {{ __('Submission closed. Deadline was ') . $submissionDeadline->format('d M Y H:i') }}
    </div>
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
  @if(isset($matchEvents) && count($matchEvents) > 0)
                <div class="recorded-events">
                    <div class="events-title">
                        📋 {{ __('Recorded Events') }}
                    </div>

                    @php
                    $eventsByType = collect($matchEvents)->groupBy(function($event) {
                        return $event->additional_data['type'] ?? $event->event_type;
                    });
                    @endphp

                    @if($eventsByType->has('goal'))
                    <div class="event-type-section">
                        <div class="event-type-header goals">
                            <span>⚽</span>
                            {{ __('Goals') }} ({{ $eventsByType['goal']->count() }})
                        </div>
                        @foreach($eventsByType['goal'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name  }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this goal?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('assist'))
                    <div class="event-type-section">
                        <div class="event-type-header assists">
                            <span>🎯</span>
                            {{ __('Assists') }} ({{ $eventsByType['assist']->count() }})
                        </div>
                        @foreach($eventsByType['assist'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name  }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this assist?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('sub_out') || $eventsByType->has('sub_in'))
                    <div class="event-type-section">
                        <div class="event-type-header substitutions">
                            <span>🔄</span>
                            {{ __('Substitutions') }} ({{ ($eventsByType->get('sub_out', collect())->count() + $eventsByType->get('sub_in', collect())->count()) / 2 }})
                        </div>
                        @php
                        $subOutEvents = $eventsByType->get('sub_out', collect());
                        $subInEvents = $eventsByType->get('sub_in', collect());
                        $substitutionsByMinute = [];

                        foreach($subOutEvents as $subOut) {
                            $minute = $subOut->minute_in_match;
                            if (!isset($substitutionsByMinute[$minute])) {
                                $substitutionsByMinute[$minute] = ['out' => [], 'in' => []];
                            }
                            $substitutionsByMinute[$minute]['out'][] = $subOut;
                        }

                        foreach($subInEvents as $subIn) {
                            $minute = $subIn->minute_in_match;
                            if (!isset($substitutionsByMinute[$minute])) {
                                $substitutionsByMinute[$minute] = ['out' => [], 'in' => []];
                            }
                            $substitutionsByMinute[$minute]['in'][] = $subIn;
                        }

                        ksort($substitutionsByMinute);
                        @endphp

                        @foreach($substitutionsByMinute as $minute => $subs)
                            @if(count($subs['out']) > 0 && count($subs['in']) > 0)
                                @foreach($subs['out'] as $index => $subOut)
                                    @php $subIn = $subs['in'][$index] ?? null; @endphp
                                    @if($subIn)
                                    <div class="event-row-display">
                                        <div class="event-info">
                                            <div class="event-minute">{{ $minute }}'</div>
                                            <div class="player-details">
                                                <div class="player-name" style="color: #ef4444;">↓ {{ $subOut->player_name }}</div>
                                                <div class="club-name">{{ $subOut->club_name ?? $match->home_club_name }}</div>
                                            </div>
                                            <span style="color: #6b7280; margin: 0 8px;">→</span>
                                            <div class="player-details">
                                                <div class="player-name" style="color: #10b981;">↑ {{ $subIn->player_name }}</div>
                                                <div class="club-name">{{ $subIn->club_name ?? $match->home_club_name }}</div>
                                            </div>
                                        </div>
                                        <div style="display: flex; gap: 4px;">
                                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete SUB OUT?');">
                                                @csrf
                                                <input type="hidden" name="event_id" value="{{ $subOut->event_id }}">
                                                <button type="submit" class="btn-delete" style="font-size: 0.688rem; padding: 4px 8px;">Out</button>
                                            </form>
                                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete SUB IN?');">
                                                @csrf
                                                <input type="hidden" name="event_id" value="{{ $subIn->event_id }}">
                                                <button type="submit" class="btn-delete" style="font-size: 0.688rem; padding: 4px 8px;">In</button>
                                            </form>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('yellow_card'))
                    <div class="event-type-section">
                        <div class="event-type-header yellow">
                            <span>🟨</span>
                            {{ __('Yellow Cards') }} ({{ $eventsByType['yellow_card']->count() }})
                        </div>
                        @foreach($eventsByType['yellow_card'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this card?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('red_card'))
                    <div class="event-type-section">
                        <div class="event-type-header red">
                            <span>🟥</span>
                            {{ __('Red Cards') }} ({{ $eventsByType['red_card']->count() }})
                        </div>
                        @foreach($eventsByType['red_card'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this card?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('penalty_missed'))
                    <div class="event-type-section">
                        <div class="event-type-header penalty-missed">
                            <span>❌</span>
                            {{ __('Penalties Missed') }} ({{ $eventsByType['penalty_missed']->count() }})
                        </div>
                        @foreach($eventsByType['penalty_missed'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('penalty_saved'))
                    <div class="event-type-section">
                        <div class="event-type-header penalty-saved">
                            <span>🧤</span>
                            {{ __('Penalties Saved') }} ({{ $eventsByType['penalty_saved']->count() }})
                        </div>
                        @foreach($eventsByType['penalty_saved'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($eventsByType->has('own_goal'))
                    <div class="event-type-section">
                        <div class="event-type-header own-goal">
                            <span>⚠️</span>
                            {{ __('Own Goals') }} ({{ $eventsByType['own_goal']->count() }})
                        </div>
                        @foreach($eventsByType['own_goal'] as $event)
                        <div class="event-row-display">
                            <div class="event-info">
                                <div class="event-minute">{{ $event->minute_in_match }}'</div>
                                <div class="player-details">
                                    <div class="player-name">{{ $event->player_name }}</div>
                                    <div class="club-name">{{ $event->club_name }}</div>
                                </div>
                            </div>
                            <form action="{{ route('admin.match.deleteEvent') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                <input type="hidden" name="event_id" value="{{ $event->event_id }}">
                                <button type="submit" class="btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endif
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
                            <div class="action-type-badge goal">⚽ {{ __('Goals') }}</div>
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
                                        <input type="number" name="goals[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('goals', 'home')">+ {{ __('Add Goal') }}</button>
                        </div>

                        <!-- Assists HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge assist">🎯 {{ __('Assists') }}</div>
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
                                        <input type="number" name="assists[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('assists', 'home')">+ {{ __('Add Assist') }}</button>
                        </div>

                        <!-- Substitutions HOME -->
                        <div class="action-player-select" style="grid-column: 1 / -1;">
                            <div class="action-type-badge substitution">🔄 {{ __('Substitutions') }}</div>
                            <div id="substitutions-container-home">
                                <div class="substitution-row">
                                    <div>
                                        <label class="sub-direction-label sub-out-label">{{ __('Player Out') }}</label>
                                        <select name="substitutions[0][player_out_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersHome as $player)
                                            @if (in_array($player->id, $starterIdsHome))
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
                                            @if (in_array($player->id, $subIdsHome))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="substitutions[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addSubstitutionRow('home')">+ {{ __('Add Substitution') }}</button>
                        </div>

                        <!-- Yellow Cards HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge yellow">🟨 {{ __('Yellow Cards') }}</div>
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
                                        <input type="number" name="yellow_cards[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('yellow_cards', 'home')">+ {{ __('Add Yellow Card') }}</button>
                        </div>

                        <!-- Red Cards HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge red">🟥 {{ __('Red Cards') }}</div>
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
                                        <input type="number" name="red_cards[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('red_cards', 'home')">+ {{ __('Add Red Card') }}</button>
                        </div>

                        <!-- Penalty Missed HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge penalty-missed">❌ {{ __('Penalty Missed') }}</div>
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
                                        <input type="number" name="penalty_missed[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('penalty_missed', 'home')">+ {{ __('Add Penalty Missed') }}</button>
                        </div>

                        <!-- Penalty Saved HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge penalty-saved">🧤 {{ __('Penalty Saved') }}</div>
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
                                        <input type="number" name="penalty_saved[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('penalty_saved', 'home')">+ {{ __('Add Penalty Saved') }}</button>
                        </div>

                        <!-- Own Goals HOME -->
                        <div class="action-player-select">
                            <div class="action-type-badge own-goal">⚠️ {{ __('Own Goal') }}</div>
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
                                        <input type="number" name="own_goals[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('own_goals', 'home')">+ {{ __('Add Own Goal') }}</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-actions">💾 {{ __('Save Match Actions') }}</button>
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
                            <div class="action-type-badge goal">⚽ {{ __('Goals') }}</div>
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
                                        <input type="number" name="goals[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('goals', 'away')">+ {{ __('Add Goal') }}</button>
                        </div>

                        <!-- Assists AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge assist">🎯 {{ __('Assists') }}</div>
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
                                        <input type="number" name="assists[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('assists', 'away')">+ {{ __('Add Assist') }}</button>
                        </div>

                        <!-- Substitutions AWAY -->
                        <div class="action-player-select" style="grid-column: 1 / -1;">
                            <div class="action-type-badge substitution">🔄 {{ __('Substitutions') }}</div>
                            <div id="substitutions-container-away">
                                <div class="substitution-row">
                                    <div>
                                        <label class="sub-direction-label sub-out-label">{{ __('Player Out') }}</label>
                                        <select name="substitutions[0][player_out_id]" class="form-control action-input">
                                            <option value="">{{ __('Select Player') }}</option>
                                            @foreach ($playersAway as $player)
                                            @if (in_array($player->id, $starterIdsAway))
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
                                            @if (in_array($player->id, $subIdsAway))
                                            <option value="{{ $player->id }}">{{ $player->name }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="minute-input-group">
                                        <label>{{ __('Minute') }}</label>
                                        <input type="number" name="substitutions[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addSubstitutionRow('away')">+ {{ __('Add Substitution') }}</button>
                        </div>

                        <!-- Yellow Cards AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge yellow">🟨 {{ __('Yellow Cards') }}</div>
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
                                        <input type="number" name="yellow_cards[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('yellow_cards', 'away')">+ {{ __('Add Yellow Card') }}</button>
                        </div>

                        <!-- Red Cards AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge red">🟥 {{ __('Red Cards') }}</div>
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
                                        <input type="number" name="red_cards[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('red_cards', 'away')">+ {{ __('Add Red Card') }}</button>
                        </div>

                        <!-- Penalty Missed AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge penalty-missed">❌ {{ __('Penalty Missed') }}</div>
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
                                        <input type="number" name="penalty_missed[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('penalty_missed', 'away')">+ {{ __('Add Penalty Missed') }}</button>
                        </div>

                        <!-- Penalty Saved AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge penalty-saved">🧤 {{ __('Penalty Saved') }}</div>
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
                                        <input type="number" name="penalty_saved[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('penalty_saved', 'away')">+ {{ __('Add Penalty Saved') }}</button>
                        </div>

                        <!-- Own Goals AWAY -->
                        <div class="action-player-select">
                            <div class="action-type-badge own-goal">⚠️ {{ __('Own Goal') }}</div>
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
                                        <input type="number" name="own_goals[0][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                                    </div>
                                    <button type="button" class="btn-remove-action" onclick="removeActionRow(this)" style="display: none;">✕</button>
                                </div>
                            </div>
                            <button type="button" class="btn-add-action" onclick="addActionRow('own_goals', 'away')">+ {{ __('Add Own Goal') }}</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-save-actions">💾 {{ __('Save Match Actions') }}</button>
                </form>
            </div>
            @else
            <div class="no-lineup-message">
                ⚠️ {{ __('No lineup submitted for') }} {{ $match->away_club_name }}
            </div>
            @endif
        </div>
    </div>
</div>

@endif

@endsection

@if($match)
@section('scripts')
<script>
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

    const starterPlayersHome = @json($playersHome->filter(function($player) use($starterIdsHome) {
        return in_array($player->id, $starterIdsHome);
    })->values() ?? []);

    const subPlayersHome = @json($playersHome->filter(function($player) use($subIdsHome) {
        return in_array($player->id, $subIdsHome);
    })->values() ?? []);

    const starterPlayersAway = @json($playersAway->filter(function($player) use($starterIdsAway) {
        return in_array($player->id, $starterIdsAway);
    })->values() ?? []);

    const subPlayersAway = @json($playersAway->filter(function($player) use($subIdsAway) {
        return in_array($player->id, $subIdsAway);
    })->values() ?? []);

    function addActionRow(actionType, team) {
        const container = document.getElementById(`${actionType}-container-${team}`);
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
                    <input type="number" name="${actionType}[${index}][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                </div>
                <button type="button" class="btn-remove-action" onclick="removeActionRow(this)">✕</button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        actionCounters[actionType]++;
        updateRemoveButtons(actionType, team);
    }

    function addSubstitutionRow(team) {
        const container = document.getElementById(`substitutions-container-${team}`);
        const index = actionCounters.substitutions;
        const starterPlayers = team === 'home' ? starterPlayersHome : starterPlayersAway;
        const subPlayers = team === 'home' ? subPlayersHome : subPlayersAway;

        let playerOutOptions = '<option value="">Select Player</option>';
        starterPlayers.forEach(player => {
            playerOutOptions += `<option value="${player.id}">${player.name}</option>`;
        });

        let playerInOptions = '<option value="">Select Player</option>';
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
                    <input type="number" name="substitutions[${index}][minute]" class="form-control action-input" placeholder="45" min="1" max="120">
                </div>
                <button type="button" class="btn-remove-action" onclick="removeActionRow(this)">✕</button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);
        actionCounters.substitutions++;
        updateRemoveButtons('substitutions', team);
    }

    function removeActionRow(button) {
        button.closest('.action-row, .substitution-row').remove();
    }

    function updateRemoveButtons(actionType, team) {
        const container = document.getElementById(`${actionType}-container-${team}`);
        if (!container) return;

        const rows = container.querySelectorAll('.action-row, .substitution-row');
        rows.forEach((row) => {
            const removeBtn = row.querySelector('.btn-remove-action');
            if (rows.length > 1) {
                removeBtn.style.display = 'flex';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
</script>
@endsection
@endif