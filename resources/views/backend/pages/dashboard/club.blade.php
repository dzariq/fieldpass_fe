@extends('backend.layouts.master')

@section('title')
{{ $club->name }} - Club Dashboard
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">
                    @if($club->avatar)
                        <img src="{{ asset($club->avatar) }}" alt="{{ $club->name }}" class="club-logo mr-2">
                    @endif
                    {{ $club->name }} Dashboard
                </h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="index.html">Home</a></li>
                    <li><span>Club Dashboard</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <!-- Club Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $wins }}</h3>
                            <p class="mb-0">Wins</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $draws }}</h3>
                            <p class="mb-0">Draws</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $losses }}</h3>
                            <p class="mb-0">Losses</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $totalPassedMatches }}</h3>
                            <p class="mb-0">Total Played</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-futbol fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-home text-primary fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Home Matches</h5>
                            <h4 class="mb-0">{{ $homeMatches }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-plane text-success fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Away Matches</h5>
                            <h4 class="mb-0">{{ $awayMatches }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            <i class="fas fa-calendar-plus text-info fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Upcoming</h5>
                            <h4 class="mb-0">{{ $totalUpcomingMatches }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixtures Section -->
    <div class="row">
        <!-- Recent Results -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-header-title">
                        <i class="fas fa-history mr-2"></i>Recent Results
                    </h4>
                </div>
                <div class="card-body">
                    @if($passedFixturesByCompetition->count() > 0)
                        @foreach($passedFixturesByCompetition as $competitionName => $fixtures)
                            <!-- Competition Header -->
                            <div class="competition-header mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-trophy mr-2 text-primary"></i>
                                    {{ $competitionName }}
                                    <span class="badge badge-secondary ml-2">{{ $fixtures->count() }}</span>
                                </h6>
                                <hr class="mt-2 mb-3">
                            </div>
                            
                            <!-- Fixtures for this competition -->
                            @foreach($fixtures->take(5) as $fixture)
                                <div class="fixture-item mb-3 p-3 border rounded {{ $fixture->home_club_id == $clubId ? 'home-match' : 'away-match' }}">
                                    <div class="row align-items-center">
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->home_club_avatar)
                                                    <img src="{{ asset($fixture->home_club_avatar) }}" 
                                                         alt="{{ $fixture->home_club_name }}" 
                                                         class="club-avatar mb-1 {{ $fixture->home_club_id == $clubId ? 'my-club' : '' }}">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1 {{ $fixture->home_club_id == $clubId ? 'my-club' : '' }}">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block {{ $fixture->home_club_id == $clubId ? 'font-weight-bold text-primary' : '' }}">
                                                    {{ Str::limit($fixture->home_club_name, 12) }}
                                                    @if($fixture->home_club_id == $clubId)
                                                        <br><span class="badge badge-sm badge-primary">HOME</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="score-display">
                                                @php
                                                    $isWin = ($fixture->home_club_id == $clubId && $fixture->home_score > $fixture->away_score) || 
                                                             ($fixture->away_club_id == $clubId && $fixture->away_score > $fixture->home_score);
                                                    $isDraw = $fixture->home_score == $fixture->away_score;
                                                    $isLoss = !$isWin && !$isDraw;
                                                @endphp
                                                
                                                <div class="result-indicator mb-2">
                                                    @if($isWin)
                                                        <span class="badge badge-success">WIN</span>
                                                    @elseif($isDraw)
                                                        <span class="badge badge-warning">DRAW</span>
                                                    @else
                                                        <span class="badge badge-danger">LOSS</span>
                                                    @endif
                                                </div>
                                                
                                                <h4 class="mb-1">
                                                    <span class="home-score">{{ $fixture->home_score }}</span>
                                                    -
                                                    <span class="away-score">{{ $fixture->away_score }}</span>
                                                </h4>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::createFromTimestamp($fixture->date)->format('M j, Y H:i') }}
                                                </small>
                                                <br>
                                                <small class="badge badge-secondary">MW{{ $fixture->matchweek }}</small>
                                                <br>
                                                <small class="competition-badge badge badge-outline-primary">
                                                    {{ $fixture->competition_type }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->away_club_avatar)
                                                    <img src="{{ asset($fixture->away_club_avatar) }}" 
                                                         alt="{{ $fixture->away_club_name }}" 
                                                         class="club-avatar mb-1 {{ $fixture->away_club_id == $clubId ? 'my-club' : '' }}">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1 {{ $fixture->away_club_id == $clubId ? 'my-club' : '' }}">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block {{ $fixture->away_club_id == $clubId ? 'font-weight-bold text-primary' : '' }}">
                                                    {{ Str::limit($fixture->away_club_name, 12) }}
                                                    @if($fixture->away_club_id == $clubId)
                                                        <br><span class="badge badge-sm badge-success">AWAY</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                        
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i>View All Results
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-futbol text-muted fa-3x mb-3"></i>
                            <h5 class="text-muted">No Recent Results</h5>
                            <p class="text-muted">No matches have been completed yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Fixtures -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-header-title">
                        <i class="fas fa-calendar-alt mr-2"></i>Upcoming Fixtures
                    </h4>
                </div>
                <div class="card-body">
                    @if($upcomingFixturesByCompetition->count() > 0)
                        @foreach($upcomingFixturesByCompetition as $competitionName => $fixtures)
                            <!-- Competition Header -->
                            <div class="competition-header mb-3">
                                <h6 class="mb-0">
                                    <i class="fas fa-trophy mr-2 text-success"></i>
                                    {{ $competitionName }}
                                    <span class="badge badge-info ml-2">{{ $fixtures->count() }}</span>
                                </h6>
                                <hr class="mt-2 mb-3">
                            </div>
                            
                            <!-- Fixtures for this competition -->
                            @foreach($fixtures->take(5) as $fixture)
                                <div class="fixture-item mb-3 p-3 border rounded {{ $fixture->home_club_id == $clubId ? 'home-match' : 'away-match' }}">
                                    <div class="row align-items-center">
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->home_club_avatar)
                                                    <img src="{{ asset($fixture->home_club_avatar) }}" 
                                                         alt="{{ $fixture->home_club_name }}" 
                                                         class="club-avatar mb-1 {{ $fixture->home_club_id == $clubId ? 'my-club' : '' }}">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1 {{ $fixture->home_club_id == $clubId ? 'my-club' : '' }}">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block {{ $fixture->home_club_id == $clubId ? 'font-weight-bold text-primary' : '' }}">
                                                    {{ Str::limit($fixture->home_club_name, 12) }}
                                                    @if($fixture->home_club_id == $clubId)
                                                        <br><span class="badge badge-sm badge-primary">HOME</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="fixture-info">
                                                <h5 class="mb-1 text-muted">VS</h5>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::createFromTimestamp($fixture->date)->format('M j, Y H:i') }}
                                                </small>
                                                <br>
                                                <small class="text-success">
                                                    {{ \Carbon\Carbon::createFromTimestamp($fixture->date)->diffForHumans() }}
                                                </small>
                                                <br>
                                                <small class="badge badge-info">MW{{ $fixture->matchweek }}</small>
                                                <br>
                                                <small class="competition-badge badge badge-outline-success">
                                                    {{ $fixture->competition_type }}
                                                </small>

                                                @if(auth()->user()->can('admin.view') || auth()->user()->can('match.edit'))
                                                    <div class="fixture-actions mt-2">
                                                        <div class="btn-group btn-group-sm" role="group" aria-label="Fixture actions">
                                                            @can('admin.view')
                                                                <a class="btn btn-outline-primary"
                                                                   href="{{ route('admin.player.lineup', ['id' => $fixture->id]) }}">
                                                                    <i class="fas fa-users"></i> Lineup
                                                                </a>
                                                            @endcan
                                                            @can('match.edit')
                                                                <a class="btn btn-outline-secondary"
                                                                   href="{{ route('admin.match.match_info', ['id' => $fixture->id]) }}">
                                                                    <i class="fas fa-edit"></i> Match Update
                                                                </a>
                                                            @endcan
                                                            <a class="btn btn-outline-dark"
                                                               target="_blank"
                                                               rel="noopener noreferrer"
                                                               href="{{ route('admin.matches.lineups-print', $fixture->id) }}">
                                                                <i class="fas fa-print"></i> {{ __('Print lineups') }}
                                                            </a>
                                                        </div>

                                                        @can('admin.view')
                                                            @php
                                                                $myClubIsHome = (int) $fixture->home_club_id === (int) $clubId;
                                                                $myLineupSubmitted = $myClubIsHome
                                                                    ? (isset($fixture->home_lineup_submitted) ? (int) $fixture->home_lineup_submitted : null)
                                                                    : (isset($fixture->away_lineup_submitted) ? (int) $fixture->away_lineup_submitted : null);
                                                            @endphp
                                                            @if($myLineupSubmitted === 0)
                                                                <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small">
                                                                    <i class="fas fa-exclamation-triangle"></i> Your club lineup not submitted
                                                                </div>
                                                            @endif
                                                        @endcan
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->away_club_avatar)
                                                    <img src="{{ asset($fixture->away_club_avatar) }}" 
                                                         alt="{{ $fixture->away_club_name }}" 
                                                         class="club-avatar mb-1 {{ $fixture->away_club_id == $clubId ? 'my-club' : '' }}">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1 {{ $fixture->away_club_id == $clubId ? 'my-club' : '' }}">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block {{ $fixture->away_club_id == $clubId ? 'font-weight-bold text-primary' : '' }}">
                                                    {{ Str::limit($fixture->away_club_name, 12) }}
                                                    @if($fixture->away_club_id == $clubId)
                                                        <br><span class="badge badge-sm badge-success">AWAY</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                        
                        <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-calendar mr-1"></i>View All Fixtures
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-plus text-muted fa-3x mb-3"></i>
                            <h5 class="text-muted">No Upcoming Fixtures</h5>
                            <p class="text-muted">No fixtures have been scheduled yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<style>
    .club-logo {
        width: 30px;
        height: 30px;
        object-fit: cover;
        border-radius: 50%;
    }
    
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }
    
    .card {
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: 1px solid #e3e6f0;
    }
    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
    }
    
    /* Fixture Styles */
    .fixture-item {
        background-color: #fafafa;
        transition: all 0.3s ease;
    }
    .fixture-item:hover {
        background-color: #f1f1f1;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    /* Highlight club's own matches */
    .home-match {
        border-left: 4px solid #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }
    .away-match {
        border-left: 4px solid #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }
    
    .club-avatar {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e3e6f0;
    }
    
    .club-avatar.my-club {
        border: 3px solid #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
    }
    
    .club-avatar-placeholder {
        width: 40px;
        height: 40px;
        background-color: #e3e6f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: #6c757d;
    }
    
    .club-avatar-placeholder.my-club {
        background-color: #007bff;
        color: white;
        border: 3px solid #0056b3;
    }
    
    .score-display h4 {
        font-weight: bold;
        color: #495057;
    }
    
    .home-score, .away-score {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .club-info small {
        font-size: 0.75rem;
        color: #6c757d;
        font-weight: 500;
    }
    
    .fixture-info h5 {
        font-size: 1.2rem;
        font-weight: 600;
    }

    .fixture-actions .btn {
        white-space: nowrap;
    }

    /* Fixture action buttons: modern + mobile-first */
    .fixture-actions .btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
    }
    .fixture-actions .btn-group > .btn {
        float: none;
        flex: 1 1 auto;
        border-radius: 10px !important;
        padding: 8px 10px;
        font-weight: 700;
        font-size: 0.82rem;
        line-height: 1.1;
        border-width: 1px;
        box-shadow: none;
    }
    .fixture-actions .btn-outline-primary {
        background: rgba(37, 99, 235, 0.06);
        border-color: rgba(37, 99, 235, 0.35);
        color: #1d4ed8;
    }
    .fixture-actions .btn-outline-primary:hover {
        background: rgba(37, 99, 235, 0.12);
        border-color: rgba(37, 99, 235, 0.55);
        color: #1d4ed8;
    }
    .fixture-actions .btn-outline-secondary {
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.35);
        color: #334155;
    }
    .fixture-actions .btn-outline-secondary:hover {
        background: rgba(100, 116, 139, 0.14);
        border-color: rgba(100, 116, 139, 0.55);
        color: #334155;
    }
    @media (max-width: 576px) {
        .fixture-actions .btn-group {
            flex-direction: column;
            align-items: stretch;
        }
        .fixture-actions .btn-group > .btn {
            width: 100%;
        }
    }
    
    /* Competition Header Styles */
    .competition-header {
        border-left: 4px solid #007bff;
        padding-left: 12px;
        background-color: rgba(0, 123, 255, 0.05);
        margin-left: -15px;
        margin-right: -15px;
        padding-top: 8px;
        padding-bottom: 4px;
        padding-right: 15px;
    }
    
    .competition-header h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .competition-header hr {
        border-color: #dee2e6;
        margin-top: 8px;
        margin-bottom: 12px;
    }
    
    /* Competition Badge Styles */
    .competition-badge {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    
    .badge-outline-primary {
        color: #007bff;
        border: 1px solid #007bff;
        background-color: rgba(0, 123, 255, 0.1);
    }
    
    .badge-outline-success {
        color: #28a745;
        border: 1px solid #28a745;
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    /* Result indicator */
    .result-indicator {
        margin-bottom: 8px;
    }
    
    .badge-sm {
        font-size: 0.6rem;
        padding: 2px 6px;
    }
</style>