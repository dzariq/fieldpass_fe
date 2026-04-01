@extends('backend.layouts.master')

@section('title')
{{ __('Lineup - vs ' . !$match ? $opponentTeamName : '') }}
@endsection
@php
$usr = Auth::guard('admin')->user();
@endphp
@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<style>
    body {
        background: linear-gradient(135deg, #0f4c3a 0%, #1a7f64 100%);
        min-height: 100vh;
        font-size: 13px;
    }

    .page-title-area {
        background: linear-gradient(135deg, rgba(15, 76, 58, 0.95), rgba(26, 127, 100, 0.95));
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 15px;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .page-title-area .row {
        align-items: center;
    }

    .page-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: white;
        margin-bottom: 5px;
    }

    .page-title-area p {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 2px;
        font-size: 12px;
    }

    .main-content-inner {
        background: rgba(255, 255, 255, 0.97);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }

    .alert {
        border-radius: 8px;
        border: none;
        font-weight: 500;
        padding: 10px 15px;
        font-size: 12px;
        margin-bottom: 15px;
    }

    .alert-danger {
        background: linear-gradient(135deg, #dc3545, #c82333);
        color: white;
    }

    .alert-info {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
    }

    .lineup-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
    }

    .lineup-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        border: 1px solid #e9ecef;
    }

    .lineup-section h5 {
        color: #0f4c3a;
        font-size: 1rem;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 700;
    }

    .section-icon {
        width: 22px;
        height: 22px;
        background: #0f4c3a;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 11px;
        font-weight: bold;
    }

    .form-group {
        margin-bottom: 12px;
    }

    .form-group label {
        font-weight: 600;
        color: #0f4c3a;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
    }

    .position-number {
        background: #0f4c3a;
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        min-width: 20px;
    }

    .form-control.player-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        font-size: 13px;
        background: white;
        transition: all 0.2s ease;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 10px center;
        background-repeat: no-repeat;
        background-size: 14px;
        padding-right: 35px;
        min-height: 40px;
        line-height: 1.3;
    }

    .form-control.player-select:focus {
        outline: none;
        border-color: #0f4c3a;
        box-shadow: 0 0 0 2px rgba(15, 76, 58, 0.1);
    }

    .goalkeeper-section {
        background: linear-gradient(135deg, #ff6b35, #f7931e);
        color: white;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 12px;
    }

    .goalkeeper-section label {
        color: white !important;
    }

    .goalkeeper-section .form-control.player-select {
        border-color: rgba(255, 255, 255, 0.3);
        background-color: rgba(255, 255, 255, 0.97);
    }

    .substitutes-section {
        background: #e8f5e8;
    }

    .substitutes-section .section-icon {
        background: #28a745;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0f4c3a, #1a7f64);
        border: none;
        padding: 10px 25px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(15, 76, 58, 0.25);
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0a3429, #146b54);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 76, 58, 0.35);
    }

    .deadline-warning {
        background: linear-gradient(45deg, #ff6b35, #f7931e);
        padding: 10px 15px;
        border-radius: 8px;
        margin: 15px 0;
        color: white;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 8px rgba(255, 107, 53, 0.25);
        font-size: 13px;
    }

    .admin-override-notice {
        background: linear-gradient(135deg, #17a2b8, #138496);
        padding: 10px 15px;
        border-radius: 8px;
        margin: 15px 0;
        color: white;
        font-weight: 600;
        text-align: center;
        box-shadow: 0 2px 8px rgba(23, 162, 184, 0.25);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
    }

    .admin-badge {
        background: rgba(255, 255, 255, 0.25);
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
    }

    .formation-visual {
        background: linear-gradient(to bottom, #2d8f47, #4caf50);
        border-radius: 10px;
        padding: 15px;
        margin: 15px 0;
        position: relative;
        min-height: 140px;
        overflow: hidden;
    }

    .formation-visual::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            linear-gradient(90deg, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
        background-size: 15px 15px;
    }

    .field-lines {
        position: absolute;
        top: 10%;
        left: 10%;
        right: 10%;
        bottom: 10%;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
    }

    .field-lines::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%);
    }

    .field-lines::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 60px;
        height: 60px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: translate(-50%, -50%);
    }

    .formation-preview {
        position: relative;
        z-index: 2;
        color: white;
        font-weight: bold;
        text-align: center;
        padding: 12px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 10px;
        align-items: center;
        min-height: 120px;
    }

    .player-avatar {
        background: rgba(255, 255, 255, 0.97);
        border-radius: 50%;
        width: 55px;
        height: 55px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #0f4c3a;
        font-size: 9px;
        font-weight: bold;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
        position: relative;
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .player-avatar:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .player-avatar::before {
        content: '👤';
        font-size: 20px;
        margin-bottom: 2px;
    }

    .player-name {
        font-size: 8px;
        text-align: center;
        line-height: 1;
        max-width: 50px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding: 0 2px;
    }

    .player-count {
        font-size: 11px;
        color: #6c757d;
        margin-left: 6px;
        font-weight: 500;
    }

    .form-control.player-select.selected {
        border-color: #28a745;
        background: #f8fff9;
    }

    .form-control.player-select option:disabled {
        opacity: 0.5;
    }

    /* Compact spacing */
    .mt-3 {
        margin-top: 15px !important;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .lineup-container {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .page-title {
            font-size: 1.1rem;
        }

        .page-title-area {
            padding: 12px 15px;
        }

        .main-content-inner {
            padding: 15px;
        }

        .formation-visual {
            min-height: 100px;
        }

        .player-avatar {
            width: 45px;
            height: 45px;
        }

        .player-avatar::before {
            font-size: 16px;
        }

        .player-name {
            font-size: 7px;
        }
    }

    /* Even more compact for larger screens */
    @media (min-width: 1200px) {
        .lineup-container {
            gap: 20px;
        }

        .lineup-section {
            padding: 18px;
        }
    }

    /* Tighter form controls */
    select.form-control.player-select {
        height: 40px;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    /* Compact buttons */
    button[type="submit"] {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
</style>
@endsection

@section('admin-content')

@if(!$match)
<!-- No Match Available -->
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

<div class="no-match-container">
    <div class="main-content-inner">
        <div class="no-match-icon">⚽</div>
        <h2 class="no-match-title">{{ __('No Upcoming Match') }}</h2>
        <p class="no-match-description">
            {{ __('There are currently no scheduled matches available for lineup submission.') }}
        </p>
        <a href="{{ route('admin.dashboard') }}" class="btn-back-dashboard">
            <span>←</span>
            {{ __('Back to Dashboard') }}
        </a>
    </div>
</div>
@else

@php
$now = \Carbon\Carbon::now('Asia/Kuala_Lumpur');
$matchDate = \Carbon\Carbon::createFromTimestamp($match->date)->setTimezone('Asia/Kuala_Lumpur');
$submissionDeadline = $matchDate->copy()->subHours(24);
$deadlinePassed = $now->gt($submissionDeadline);

// Check if user has permission to bypass deadline
$canBypassDeadline = $usr->can('club.create');
$isEditingAllowed = !$deadlinePassed || $canBypassDeadline;

$starterIds = $existingLineup ? [
    $existingLineup->gk,
    $existingLineup->player1,
    $existingLineup->player2,
    $existingLineup->player3,
    $existingLineup->player4,
    $existingLineup->player5,
    $existingLineup->player6,
    $existingLineup->player7,
    $existingLineup->player8,
    $existingLineup->player9,
    $existingLineup->player10,
] : [];

$subIds = $existingLineup ? [
    $existingLineup->sub1,
    $existingLineup->sub2,
    $existingLineup->sub3,
    $existingLineup->sub4,
    $existingLineup->sub5,
    $existingLineup->sub6,
    $existingLineup->sub7,
] : [];
@endphp

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-8">
            <h4 class="page-title">⚽ {{ __('Lineup') }} - {{ __('vs') }} {{ $opponentTeamName }}</h4>
            <p>📅 {{ $matchDate->format('d M Y H:i') }} • ⏰ {{ __('Deadline:') }} {{ $submissionDeadline->format('d M Y H:i') }}</p>
        </div>
        <div class="col-sm-4 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    @if ($errors->has('duplicate'))
    <div class="alert alert-danger">
        {{ $errors->first('duplicate') }}
    </div>
    @endif

    @if ($deadlinePassed)
        @if ($canBypassDeadline)
        <div class="admin-override-notice">
            <span class="admin-badge">🔓 ADMIN</span>
            <span>{{ __('Special permission: Edit after deadline') }}</span>
        </div>
        @else
        <div class="deadline-warning">
            🚫 {{ __('Submission closed') }} - {{ $submissionDeadline->format('d M Y H:i') }}
        </div>
        @endif
    @else
    <div class="deadline-warning" id="countdown-timer">
        ⚠️ {{ __('Lineup submission deadline approaching') }}
    </div>
    @endif

    <!-- Formation Visual Preview -->
    <div class="formation-visual">
        <div class="field-lines"></div>
        <div class="formation-preview">
            <div id="formation-display">
                {{ __('Select players to preview') }}
            </div>
        </div>
    </div>

    <form action="{{ route('admin.lineup.save') }}" method="POST" id="lineupForm">
        @csrf
        <input type="hidden" name="match_id" value="{{ $match->id }}">
        <input type="hidden" name="club_id" value="{{ $club_id }}">


        <div class="lineup-container">
            <div class="lineup-section">
                <h5>
                    <div class="section-icon">11</div>
                    {{ __('Starting 11') }}
                    <span class="player-count" id="starter-count">(0/11)</span>
                </h5>

                @for ($i = 1; $i <= 11; $i++)
                <div class="form-group {{ $i == 1 ? 'goalkeeper-section' : '' }}">
                    <label>
                        <div class="position-number">{{ $i == 1 ? 'GK' : $i }}</div>
                        {{ $i == 1 ? '🥅 Goalkeeper' : "⚽ Position #$i" }}
                    </label>
                    <select name="starters[]" class="form-control player-select"
                        {{ !$isEditingAllowed ? 'disabled' : 'required' }}
                        data-position="{{ $i }}">
                        <option value="">{{ __('Select Player') }}</option>
                        @foreach ($players as $player)
                            @if ($i == 1 && $player->position == 'Goalkeeper')
                                <option value="{{ $player->id }}"
                                    {{ (old("starters.$i") ?? ($starterIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} - {{ $player->position }}
                                </option>
                            @elseif ($i != 1 && $player->position != 'Goalkeeper')
                                <option value="{{ $player->id }}"
                                    {{ (old("starters.$i") ?? ($starterIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                                    {{ $player->name }} - {{ $player->position }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                @endfor
            </div>

            <div class="lineup-section substitutes-section">
                <h5>
                    <div class="section-icon">S</div>
                    {{ __('Substitutes') }}
                    <span class="player-count" id="sub-count">(0/7)</span>
                </h5>

                @for ($i = 1; $i <= 7; $i++)
                <div class="form-group">
                    <label>
                        <div class="position-number">S{{ $i }}</div>
                        {{ "🔄 Sub #$i" }}
                    </label>
                    <select name="subs[]" class="form-control player-select sub-select"
                        {{ !$isEditingAllowed ? 'disabled' : 'required' }}
                        data-position="sub{{ $i }}">
                        <option value="">{{ __('Select Player') }}</option>
                        @foreach ($players as $player)
                            <option value="{{ $player->id }}"
                                {{ (old("subs.$i") ?? ($subIds[$i - 1] ?? '')) == $player->id ? 'selected' : '' }}>
                                {{ $player->name }} ({{ $player->email }}) - {{ $player->position }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endfor
            </div>
        </div>

        @if ($isEditingAllowed)
        <button type="submit" class="btn btn-primary mt-3" id="saveButton">
            💾 {{ __('Save Lineup') }}
        </button>
        @if ($deadlinePassed && $canBypassDeadline)
        <div class="alert alert-info mt-3">
            ℹ️ {{ __('Editing after deadline with admin privileges') }}
        </div>
        @endif
        @else
        <div class="alert alert-danger mt-3">
            🚫 {{ __('Submission closed') }} - {{ $submissionDeadline->format('d M Y H:i') }}
        </div>
        @endif
    </form>
</div>

@endif

@endsection

@if($match)
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.player-select');
        const starterSelects = document.querySelectorAll('.player-select:not(.sub-select)');
        const subSelects = document.querySelectorAll('.sub-select');
        const starterCount = document.getElementById('starter-count');
        const subCount = document.getElementById('sub-count');
        const saveButton = document.getElementById('saveButton');
        const formationDisplay = document.getElementById('formation-display');

        function updateCounts() {
            const selectedStarters = Array.from(starterSelects).filter(select => select.value !== '').length;
            const selectedSubs = Array.from(subSelects).filter(select => select.value !== '').length;

            if (starterCount) starterCount.textContent = `(${selectedStarters}/11)`;
            if (subCount) subCount.textContent = `(${selectedSubs}/7)`;

            if (saveButton) {
                const allStartersFilled = selectedStarters === 11;
                const allSubsFilled = selectedSubs === 7;

                if (allStartersFilled && allSubsFilled) {
                    saveButton.disabled = false;
                    saveButton.innerHTML = '💾 {{ __("Save Lineup") }}';
                } else {
                    saveButton.disabled = selectedStarters === 0 && selectedSubs === 0 ? false : true;
                    saveButton.innerHTML = `💾 {{ __("Save") }} (${selectedStarters + selectedSubs}/18)`;
                }
            }
        }

        function updateDropdowns() {
            const selectedValues = Array.from(selects).map(select => select.value).filter(v => v !== '');

            selects.forEach(select => {
                const currentValue = select.value;

                Array.from(select.options).forEach(option => {
                    if (option.value === '' || option.value === currentValue) {
                        option.disabled = false;
                        option.style.opacity = '1';
                    } else if (selectedValues.includes(option.value)) {
                        option.disabled = true;
                        option.style.opacity = '0.5';
                    } else {
                        option.disabled = false;
                        option.style.opacity = '1';
                    }
                });

                if (select.value !== '') {
                    select.classList.add('selected');
                } else {
                    select.classList.remove('selected');
                }
            });

            updateCounts();
            updateFormationDisplay();
        }

        function updateFormationDisplay() {
            if (!formationDisplay) return;

            const selectedPlayers = Array.from(selects)
                .filter(select => select.value !== '')
                .map(select => {
                    const option = select.options[select.selectedIndex];
                    const playerName = option.text.split(' (')[0].split(' - ')[0];
                    return playerName;
                });

            if (selectedPlayers.length > 0) {
                const playerAvatars = selectedPlayers.map(name => `
                    <div class="player-avatar">
                        <div class="player-name">${name}</div>
                    </div>
                `).join('');

                formationDisplay.innerHTML = `
                    <div style="font-size: 12px; margin-bottom: 10px;">
                        <strong>${selectedPlayers.length}/18 {{ __('Selected') }}</strong>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 8px;">
                        ${playerAvatars}
                    </div>
                `;
            } else {
                formationDisplay.innerHTML = `
                    <div style="font-size: 13px; opacity: 0.8;">
                        {{ __("Select players to preview") }}
                    </div>
                `;
            }
        }

        selects.forEach(select => {
            select.addEventListener('change', function() {
                updateDropdowns();
                if (this.value !== '') {
                    this.style.transform = 'scale(1.01)';
                    setTimeout(() => this.style.transform = 'scale(1)', 120);
                }
            });
        });

        if (document.getElementById('lineupForm')) {
            document.getElementById('lineupForm').addEventListener('submit', function(e) {
                if (saveButton) {
                    saveButton.innerHTML = '⏳ {{ __("Saving...") }}';
                    saveButton.disabled = true;
                }
            });
        }

        @if(!$deadlinePassed)
        function updateCountdown() {
            const deadline = new Date('{{ $submissionDeadline->toISOString() }}');
            const countdownElement = document.getElementById('countdown-timer');

            if (!countdownElement) return;

            function updateTimer() {
                const now = new Date();
                const timeDiff = deadline - now;

                if (timeDiff > 0) {
                    const hours = Math.floor(timeDiff / (1000 * 60 * 60));
                    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                    countdownElement.innerHTML = `⚠️ {{ __('Closes in') }} ${hours}h ${minutes}m`;
                } else {
                    countdownElement.innerHTML = '🚫 {{ __("Deadline passed") }}';
                    countdownElement.style.background = 'linear-gradient(45deg, #dc3545, #c82333)';
                    @if(!$canBypassDeadline)
                    if (saveButton) saveButton.disabled = true;
                    @endif
                }
            }

            updateTimer();
            setInterval(updateTimer, 60000);
        }

        updateCountdown();
        @endif

        updateDropdowns();
    });
</script>
@endsection
@endif