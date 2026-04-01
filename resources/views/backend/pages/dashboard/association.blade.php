@extends('backend.layouts.master')

@section('title')
Dashboard Page - Admin Panel
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Dashboard</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="index.html">Home</a></li>
                    <li><span>Dashboard</span></li>
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
    <!-- Competition Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $totalActive }}</h3>
                            <p class="mb-0">Total Active</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-trophy fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0">{{ $leagues }}</h3>
                            <p class="mb-0">Leagues</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-medal fa-2x"></i>
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
                            <h3 class="mb-0">{{ $cups }}</h3>
                            <p class="mb-0">Cups</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-award fa-2x"></i>
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
                            <h3 class="mb-0">{{ $tournaments }}</h3>
                            <p class="mb-0">Tournaments</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-crown fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Competitions Overview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-header-title">
                        <i class="fas fa-futbol mr-2"></i>Active Competitions Overview
                    </h4>
                </div>
                <div class="card-body">
                    @if($competitions->count() > 0)
                        <!-- Competition Types Tabs -->
                        <ul class="nav nav-tabs mb-4" id="competitionTabs" role="tablist">
                            @if($competitionsByType->has('LEAGUE'))
                                <li class="nav-item">
                                    <a class="nav-link active" id="leagues-tab" data-toggle="tab" href="#leagues" role="tab">
                                        <i class="fas fa-medal mr-1"></i>Leagues ({{ $leagues }})
                                    </a>
                                </li>
                            @endif
                            @if($competitionsByType->has('CUP'))
                                <li class="nav-item">
                                    <a class="nav-link {{ !$competitionsByType->has('LEAGUE') ? 'active' : '' }}" id="cups-tab" data-toggle="tab" href="#cups" role="tab">
                                        <i class="fas fa-award mr-1"></i>Cups ({{ $cups }})
                                    </a>
                                </li>
                            @endif
                            @if($competitionsByType->has('TOURNAMENT'))
                                <li class="nav-item">
                                    <a class="nav-link {{ !$competitionsByType->has('LEAGUE') && !$competitionsByType->has('CUP') ? 'active' : '' }}" id="tournaments-tab" data-toggle="tab" href="#tournaments" role="tab">
                                        <i class="fas fa-crown mr-1"></i>Tournaments ({{ $tournaments }})
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="competitionTabsContent">
                            @if($competitionsByType->has('LEAGUE'))
                                <div class="tab-pane fade show active" id="leagues" role="tabpanel">
                                    <div class="row">
                                        @foreach($competitionsByType['LEAGUE'] as $competition)
                                            <div class="col-lg-4 col-md-6 mb-3">
                                                <div class="card border-left-primary">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <i class="fas fa-medal text-primary fa-2x"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="mb-1">{{ $competition->name }}</h5>
                                                                <span class="badge badge-success">{{ $competition->status }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($competitionsByType->has('CUP'))
                                <div class="tab-pane fade {{ !$competitionsByType->has('LEAGUE') ? 'show active' : '' }}" id="cups" role="tabpanel">
                                    <div class="row">
                                        @foreach($competitionsByType['CUP'] as $competition)
                                            <div class="col-lg-4 col-md-6 mb-3">
                                                <div class="card border-left-warning">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <i class="fas fa-award text-warning fa-2x"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="mb-1">{{ $competition->name }}</h5>
                                                                <span class="badge badge-success">{{ $competition->status }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($competitionsByType->has('TOURNAMENT'))
                                <div class="tab-pane fade {{ !$competitionsByType->has('LEAGUE') && !$competitionsByType->has('CUP') ? 'show active' : '' }}" id="tournaments" role="tabpanel">
                                    <div class="row">
                                        @foreach($competitionsByType['TOURNAMENT'] as $competition)
                                            <div class="col-lg-4 col-md-6 mb-3">
                                                <div class="card border-left-info">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="mr-3">
                                                                <i class="fas fa-crown text-info fa-2x"></i>
                                                            </div>
                                                            <div>
                                                                <h5 class="mb-1">{{ $competition->name }}</h5>
                                                                <span class="badge badge-success">{{ $competition->status }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-futbol text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-muted">No Active Competitions</h4>
                            <p class="text-muted">There are currently no active competitions in your association.</p>
                            <a href="{{ route('admin.competitions.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus mr-2"></i>Create New Competition
</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Fixtures Section -->
    <div class="row mt-4">
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
                                <div class="fixture-item mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->home_club_avatar)
                                                    <img src="{{ asset($fixture->home_club_avatar) }}" 
                                                         alt="{{ $fixture->home_club_name }}" 
                                                         class="club-avatar mb-1">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block">{{ Str::limit($fixture->home_club_name, 12) }}</small>
                                                <!-- Home Club Lineup Status -->
                                                @if(isset($fixture->home_lineup_submitted))
                                                <div class="lineup-status mt-1">
                                                    @if($fixture->home_lineup_submitted)
                                                        <span class="badge badge-success badge-sm">
                                                            <i class="fas fa-check"></i> Ready
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning badge-sm">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="score-display">
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
                                                
                                                <!-- Overall Match Status -->
                                                @if(isset($fixture->home_lineup_submitted) && isset($fixture->away_lineup_submitted))
                                                <div class="match-status mt-2">
                                                    @if($fixture->home_lineup_submitted && $fixture->away_lineup_submitted)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-users"></i> Both Ready
                                                        </span>
                                                    @elseif($fixture->home_lineup_submitted || $fixture->away_lineup_submitted)
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-user-friends"></i> Partial
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-users-slash"></i> No Lineups
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->away_club_avatar)
                                                    <img src="{{ asset($fixture->away_club_avatar) }}" 
                                                         alt="{{ $fixture->away_club_name }}" 
                                                         class="club-avatar mb-1">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block">{{ Str::limit($fixture->away_club_name, 12) }}</small>
                                                <!-- Away Club Lineup Status -->
                                                @if(isset($fixture->away_lineup_submitted))
                                                <div class="lineup-status mt-1">
                                                    @if($fixture->away_lineup_submitted)
                                                        <span class="badge badge-success badge-sm">
                                                            <i class="fas fa-check"></i> Ready
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning badge-sm">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                        
                        <!-- <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i>View All Results
                            </a>
                        </div> -->
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
                                <div class="fixture-item mb-3 p-3 border rounded">
                                    <div class="row align-items-center">
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->home_club_avatar)
                                                    <img src="{{ asset($fixture->home_club_avatar) }}" 
                                                         alt="{{ $fixture->home_club_name }}" 
                                                         class="club-avatar mb-1">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block">{{ Str::limit($fixture->home_club_name, 12) }}</small>
                                                <!-- Home Club Lineup Status -->
                                                @if(isset($fixture->home_lineup_submitted))
                                                <div class="lineup-status mt-1">
                                                    @if($fixture->home_lineup_submitted)
                                                        <span class="badge badge-success badge-sm">
                                                            <i class="fas fa-check"></i> Ready
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger badge-sm">
                                                            <i class="fas fa-exclamation"></i> Missing
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
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
                                                
                                                <!-- Match Readiness Status -->
                                                @if(isset($fixture->home_lineup_submitted) && isset($fixture->away_lineup_submitted))
                                                <div class="match-readiness mt-2">
                                                    @if($fixture->home_lineup_submitted && $fixture->away_lineup_submitted)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-play"></i> Ready to Play
                                                        </span>
                                                    @elseif($fixture->home_lineup_submitted || $fixture->away_lineup_submitted)
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-pause"></i> Waiting
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-stop"></i> Not Ready
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-3 text-center">
                                            <div class="club-info">
                                                @if($fixture->away_club_avatar)
                                                    <img src="{{ asset($fixture->away_club_avatar) }}" 
                                                         alt="{{ $fixture->away_club_name }}" 
                                                         class="club-avatar mb-1">
                                                @else
                                                    <div class="club-avatar-placeholder mb-1">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </div>
                                                @endif
                                                <small class="d-block">{{ Str::limit($fixture->away_club_name, 12) }}</small>
                                                <!-- Away Club Lineup Status -->
                                                @if(isset($fixture->away_lineup_submitted))
                                                <div class="lineup-status mt-1">
                                                    @if($fixture->away_lineup_submitted)
                                                        <span class="badge badge-success badge-sm">
                                                            <i class="fas fa-check"></i> Ready
                                                        </span>
                                                    @else
                                                        <span class="badge badge-danger badge-sm">
                                                            <i class="fas fa-exclamation"></i> Missing
                                                        </span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                        
                        <!-- <div class="text-center mt-3">
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-calendar mr-1"></i>View All Fixtures
                            </a>
                        </div> -->
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
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
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
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link.active {
        border-bottom: 2px solid #007bff;
        background-color: transparent;
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
    
    .club-avatar {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid #e3e6f0;
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
    
    /* Competition Type Colors */
    .competition-header.league {
        border-left-color: #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }
    
    .competition-header.cup {
        border-left-color: #ffc107;
        background-color: rgba(255, 193, 7, 0.05);
    }
    
    .competition-header.tournament {
        border-left-color: #17a2b8;
        background-color: rgba(23, 162, 184, 0.05);
    }
    
    /* Lineup Status Styles */
    .lineup-status {
        margin-top: 5px;
    }
    
    .lineup-status .badge-sm {
        font-size: 0.65rem;
        padding: 3px 6px;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .overall-lineup-status, .match-readiness {
        margin-top: 8px;
    }
    
    .overall-lineup-status .badge, .match-readiness .badge {
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Lineup Status Colors */
    .badge-success {
        background-color: #28a745 !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }
    
    .badge-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
        box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
    }
    
    .badge-danger {
        background-color: #dc3545 !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }
    
    .badge-info {
        background-color: #17a2b8 !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(23, 162, 184, 0.3);
    }
    
    .badge-secondary {
        background-color: #6c757d !important;
        color: white !important;
        box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
    }
    
    /* Animation for Status Badges */
    .lineup-status .badge, .overall-lineup-status .badge, .match-readiness .badge {
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease-in;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .lineup-status .badge-sm {
            font-size: 0.6rem;
            padding: 2px 4px;
        }
        
        .overall-lineup-status .badge, .match-readiness .badge {
            font-size: 0.65rem;
            padding: 3px 6px;
        }
    }
</style>