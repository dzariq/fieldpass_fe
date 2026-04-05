@extends('backend.layouts.master')

@section('title')
{{ __('Match details') }} — {{ $match->home_club->name }} vs {{ $match->away_club->name }}
@endsection

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
<style>
    .possession-team-btn { min-width: 160px; }
    .match-club-logo { max-width: 96px; max-height: 96px; object-fit: contain; }
    .possession-timer-panel {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #93c5fd;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 0;
    }
    .possession-timer-panel h5 { margin: 0 0 4px; font-size: 1rem; font-weight: 700; color: #1e3a8a; }
    .possession-timer-panel .possession-sub { color: #475569; font-size: 0.8125rem; margin-bottom: 12px; }
    .match-timer-display {
        font-size: 1.75rem; font-weight: 800; font-variant-numeric: tabular-nums;
        color: #1d4ed8; letter-spacing: 0.02em; line-height: 1.2;
    }
    .possession-btn-row { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 14px; align-items: center; }
    .possession-btn-row form { margin: 0; }
    .btn-possession-home, .btn-possession-away {
        min-width: 160px; font-weight: 600; padding: 10px 16px; border-radius: 10px; border: none; cursor: pointer;
    }
    .btn-possession-home { background: #2563eb; color: #fff; }
    .btn-possession-away { background: #7c3aed; color: #fff; }
    .btn-possession-home:disabled, .btn-possession-away:disabled { opacity: 0.55; cursor: not-allowed; }
    .possession-mini-stats {
        font-size: 0.8125rem; color: #334155; margin-top: 12px; padding-top: 12px; border-top: 1px solid #bfdbfe;
    }
</style>
@endsection

@php
    $canEditMatch = auth()->user()->can('match.edit');
@endphp

@section('admin-content')

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ $match->home_club->name }} vs {{ $match->away_club->name }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.matches.index') }}">{{ __('Matches') }}</a></li>
                    <li><span>{{ __('Match details') }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    <div class="container mt-4">
        @include('backend.layouts.partials.messages')

        <ul class="nav nav-tabs" id="matchTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab">{{ __('Overview') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="possession-tab" data-toggle="tab" href="#possession" role="tab">{{ __('Ball possession') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="actions-tab" data-toggle="tab" href="#actions" role="tab">{{ __('Actions') }}</a>
            </li>
        </ul>

        <div class="tab-content mt-3" id="matchTabsContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center text-center">
                            <div class="col-md-4">
                                @if ($match->home_club->avatar ?? null)
                                    <img src="{{ asset($match->home_club->avatar) }}" alt="" class="match-club-logo mb-2">
                                @endif
                                <h5 class="mb-1">{{ $match->home_club->name }}</h5>
                                <p class="h3 mb-0">{{ (int) $match->home_score }}</p>
                                <span class="badge badge-secondary">{{ __('Home') }}</span>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted small mb-1">{{ $match->competition->name ?? '—' }}</p>
                                <h3 class="mb-2">VS</h3>
                                <p class="mb-1"><strong>{{ __('Matchweek') }}</strong> {{ $match->matchweek }}</p>
                                <p class="mb-1"><strong>{{ __('Fixture date') }}</strong> {{ date('Y-m-d H:i', (int) $match->date) }}</p>
                                <p class="mb-0">
                                    <span class="badge badge-info">{{ $match->status }}</span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                @if ($match->away_club->avatar ?? null)
                                    <img src="{{ asset($match->away_club->avatar) }}" alt="" class="match-club-logo mb-2">
                                @endif
                                <h5 class="mb-1">{{ $match->away_club->name }}</h5>
                                <p class="h3 mb-0">{{ (int) $match->away_score }}</p>
                                <span class="badge badge-secondary">{{ __('Away') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="possession" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        @include('backend.pages.matches.partials.match-possession-ajax-panel', [
                            'possessionMatch' => $match,
                            'homeClubId' => (int) $match->home_club_id,
                            'awayClubId' => (int) $match->away_club_id,
                            'homeName' => $match->home_club->name,
                            'awayName' => $match->away_club->name,
                            'possessionSummary' => $summary,
                            'canEdit' => $canEditMatch,
                            'showFullLogLink' => false,
                        ])
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="actions" role="tabpanel">
                <p class="text-muted">{{ __('Use match edit tools elsewhere for lineups and live events.') }}</p>
                @if ($canEditMatch)
                    <a href="{{ route('admin.matches.edit', $match->id) }}" class="btn btn-success text-white">{{ __('Edit match') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
@include('backend.pages.matches.partials.match-possession-ajax-script')
</script>
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.bootstrap.min.js"></script>
<script>
    if ($('#dataTable').length) {
        $('#dataTable').DataTable({ responsive: true });
    }
</script>
@endsection
