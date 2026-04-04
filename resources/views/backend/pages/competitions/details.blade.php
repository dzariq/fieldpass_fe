@extends('backend.layouts.master')

@section('title')
{{ __('Competition') }} — {{ $competition->name }}
@endsection

@section('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
<style>
    .fp-comp-page {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    .fp-comp-hero {
        border-radius: 16px;
        border: 1px solid rgba(102, 126, 234, 0.2);
        overflow: hidden;
        background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);
        box-shadow: 0 12px 40px rgba(15, 23, 42, 0.08);
    }
    .fp-comp-hero__banner {
        height: 120px;
        background-size: cover;
        background-position: center;
        background-color: #6366f1;
    }
    .fp-comp-hero__banner--fallback {
        background: linear-gradient(120deg, #667eea 0%, #764ba2 55%, #a855f7 100%);
    }
    .fp-comp-hero__body {
        padding: 1.25rem 1.5rem 1.5rem;
    }
    .fp-comp-hero__logo {
        width: 88px;
        height: 88px;
        object-fit: cover;
        border-radius: 16px;
        border: 4px solid #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
        margin-top: -52px;
        background: #fff;
    }
    @media (min-width: 768px) {
        .fp-comp-hero__logo {
            margin-top: 0;
        }
    }
    .fp-comp-title {
        font-weight: 800;
        letter-spacing: -0.02em;
        color: #0f172a;
        font-size: 1.5rem;
        line-height: 1.25;
    }
    .fp-comp-meta .badge {
        font-weight: 600;
        padding: 0.35em 0.65em;
        border-radius: 999px;
        font-size: 0.75rem;
    }
    .fp-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }
    @media (min-width: 576px) {
        .fp-stat-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
    .fp-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 0.85rem 1rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }
    .fp-stat-card__icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        margin-bottom: 0.35rem;
    }
    .fp-stat-card__icon--teams { background: rgba(99, 102, 241, 0.12); color: #4f46e5; }
    .fp-stat-card__icon--fixtures { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .fp-stat-card__icon--weeks { background: rgba(245, 158, 11, 0.15); color: #d97706; }
    .fp-stat-card__icon--cap { background: rgba(236, 72, 153, 0.12); color: #db2777; }
    .fp-stat-card__val {
        font-weight: 800;
        font-size: 1.35rem;
        color: #0f172a;
        line-height: 1.2;
    }
    .fp-stat-card__lbl {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 600;
    }
    .fp-comp-tabs-wrap {
        background: #f1f5f9;
        border-radius: 14px;
        padding: 6px;
        border: 1px solid rgba(148, 163, 184, 0.35);
    }
    .fp-comp-tabs.nav-pills .nav-link {
        border-radius: 10px;
        font-weight: 700;
        font-size: 0.9rem;
        color: #475569;
        padding: 0.65rem 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    .fp-comp-tabs.nav-pills .nav-link:hover {
        background: rgba(255, 255, 255, 0.85);
        color: #1e293b;
    }
    .fp-comp-tabs.nav-pills .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff !important;
        box-shadow: 0 6px 18px rgba(102, 126, 234, 0.45);
    }
    .fp-comp-tabs.nav-pills .nav-link i {
        opacity: 0.95;
    }
    .fp-tab-panel {
        padding-top: 0.5rem;
    }
    .fp-overview-card {
        border-radius: 14px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        box-shadow: 0 8px 28px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .fp-overview-card .card-header {
        background: linear-gradient(90deg, #f8fafc, #fff);
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        font-weight: 800;
        color: #0f172a;
    }
    .fp-dl-row dt {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 0.15rem;
    }
    .fp-dl-row dd {
        margin-bottom: 1rem;
        color: #0f172a;
        font-weight: 600;
    }
    .fp-desc {
        color: #334155;
        line-height: 1.65;
        font-size: 0.95rem;
    }
    .fp-empty-hint {
        padding: 2rem 1rem;
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
    }
    .fp-clubs-card .table thead th {
        border-top: none;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
    }
    .fp-standings-wrap {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .fp-standings-table th,
    .fp-standings-table td {
        vertical-align: middle;
    }
    .fp-standings-table th.fp-num,
    .fp-standings-table td.fp-num {
        white-space: nowrap;
    }
    .fp-standings-table .fp-standings-club {
        white-space: normal;
        min-width: 10rem;
        max-width: 20rem;
    }
    .fp-standings-hint {
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 0.75rem;
    }
    a.fp-club-summary-trigger {
        text-decoration: none;
        cursor: pointer;
        line-height: 1.3;
    }
    a.fp-club-summary-trigger:hover {
        text-decoration: underline;
        color: #4338ca !important;
    }
    .fp-club-player-photo-col {
        width: 52px;
        padding-left: 0.35rem !important;
        padding-right: 0.35rem !important;
        vertical-align: middle;
    }
    .fp-club-player-thumb {
        object-fit: cover;
        display: block;
        margin: 0 auto;
    }
    .matchweek-filter {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }
    .matchweek-filter label {
        font-weight: 700;
        color: #334155;
        margin: 0;
    }
    .matchweek-filter select.form-control {
        border-radius: 10px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        min-width: 180px;
        font-weight: 600;
    }
    .fp-match-card {
        border-radius: 14px;
        border: 1px solid rgba(16, 24, 40, 0.10);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }
    .fp-match-card__vs {
        font-weight: 900;
        font-size: 1.1rem;
        color: #334155;
        letter-spacing: -0.01em;
    }
    .fp-club-mini__name {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.1;
    }
    .fp-match-actions .btn {
        border-radius: 10px;
        padding: 10px 12px;
        font-weight: 800;
        box-shadow: none;
    }
    .fp-match-actions .btn-outline-primary {
        background: rgba(37, 99, 235, 0.06);
        border-color: rgba(37, 99, 235, 0.35);
        color: #1d4ed8;
    }
    .fp-match-actions .btn-outline-secondary {
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.35);
        color: #334155;
    }
</style>
@endsection

@section('admin-content')
@php
    $clubStandings = $clubStandings ?? [];
    $usr = Auth::guard('admin')->user();
    $matchweeks = $competition->matches()->select('matchweek')->distinct()->orderBy('matchweek', 'asc')->pluck('matchweek');
    $currentMatchweek = request()->get('matchweek', $matchweeks->first());
    $activeClubsCount = $competition->clubs->filter(fn ($c) => ($c->pivot->status ?? '') === 'ACTIVE')->count();
    $fixturesCount = $competition->matches()->count();
    $mwCount = $matchweeks->count();
    $cap = $competition->max_participants ?? null;
@endphp

<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Competition') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><a href="{{ route('admin.competitions.index') }}">{{ __('Competitions') }}</a></li>
                    <li><span>{{ Str::limit($competition->name, 42) }}</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix text-sm-right">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>

<div class="main-content-inner">
    <div class="fp-comp-page px-2 px-md-3 pb-4">

        <div class="fp-comp-hero mb-4">
            <div class="fp-comp-hero__banner {{ $competition->banner ? '' : 'fp-comp-hero__banner--fallback' }}"
                @if($competition->banner)
                    style="background-image: linear-gradient(90deg, rgba(15,23,42,0.55), rgba(15,23,42,0.25)), url('{{ asset($competition->banner) }}');"
                @endif
            ></div>
            <div class="fp-comp-hero__body">
                <div class="d-flex flex-column flex-md-row align-items-md-start">
                    <img
                        class="fp-comp-hero__logo flex-shrink-0"
                        src="{{ $competition->avatar ? asset($competition->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                        alt=""
                    >
                    <div class="flex-grow-1 ml-md-4 w-100">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-start justify-content-lg-between">
                            <div>
                                <h1 class="fp-comp-title mb-2">{{ $competition->name }}</h1>
                                <div class="fp-comp-meta d-flex flex-wrap align-items-center mb-2" style="gap: 0.5rem;">
                                    @if(!empty($competition->type))
                                        <span class="badge badge-primary">{{ $competition->type }}</span>
                                    @endif
                                    @if(!empty($competition->status))
                                        <span class="badge badge-{{ $competition->status === 'ACTIVE' ? 'success' : ($competition->status === 'COMPLETED' ? 'secondary' : 'info') }}">
                                            {{ $competition->status }}
                                        </span>
                                    @endif
                                    @if($competition->association)
                                        <span class="badge badge-light text-dark border">
                                            <i class="fas fa-landmark mr-1"></i>{{ $competition->association->name }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-muted small mb-0">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    @if(isset($competition->start) && isset($competition->end))
                                        {{ date('M j, Y', (int) $competition->start) }}
                                        —
                                        {{ date('M j, Y', (int) $competition->end) }}
                                    @else
                                        {{ __('Season dates not set') }}
                                    @endif
                                </p>
                            </div>
                            @if($usr->can('competition.edit'))
                                <a href="{{ route('admin.competitions.edit', $competition->id) }}" class="btn btn-primary mt-3 mt-lg-0 shadow-sm font-weight-bold" style="border-radius: 10px;">
                                    <i class="fas fa-edit mr-1"></i>{{ __('Edit competition') }}
                                </a>
                            @endif
                        </div>

                        <div class="fp-stat-grid mt-4">
                            <div class="fp-stat-card">
                                <div class="fp-stat-card__icon fp-stat-card__icon--teams"><i class="fas fa-shield-alt"></i></div>
                                <div class="fp-stat-card__val">{{ $activeClubsCount }}</div>
                                <div class="fp-stat-card__lbl">{{ __('Teams') }}</div>
                            </div>
                            <div class="fp-stat-card">
                                <div class="fp-stat-card__icon fp-stat-card__icon--fixtures"><i class="fas fa-futbol"></i></div>
                                <div class="fp-stat-card__val">{{ $fixturesCount }}</div>
                                <div class="fp-stat-card__lbl">{{ __('Fixtures') }}</div>
                            </div>
                            <div class="fp-stat-card">
                                <div class="fp-stat-card__icon fp-stat-card__icon--weeks"><i class="fas fa-layer-group"></i></div>
                                <div class="fp-stat-card__val">{{ $mwCount }}</div>
                                <div class="fp-stat-card__lbl">{{ __('Matchweeks') }}</div>
                            </div>
                            <div class="fp-stat-card">
                                <div class="fp-stat-card__icon fp-stat-card__icon--cap"><i class="fas fa-users"></i></div>
                                <div class="fp-stat-card__val">{{ $cap !== null ? $cap : '—' }}</div>
                                <div class="fp-stat-card__lbl">{{ __('Max clubs') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="fp-comp-tabs-wrap mb-3">
            <ul class="nav nav-pills nav-fill fp-comp-tabs" id="competitionTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">
                        <i class="fas fa-info-circle"></i><span>{{ __('Overview') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="clubs-tab" data-toggle="tab" href="#clubs" role="tab" aria-controls="clubs" aria-selected="false">
                        <i class="fas fa-table"></i><span>{{ __('Table') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="invites-tab" data-toggle="tab" href="#invites" role="tab" aria-controls="invites" aria-selected="false">
                        <i class="fas fa-futbol"></i><span>{{ __('Matches') }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="tab-content fp-tab-panel" id="competitionTabsContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <div class="row">
                    <div class="col-lg-7 mb-3 mb-lg-0">
                        <div class="card fp-overview-card h-100">
                            <div class="card-header py-3">
                                <i class="fas fa-align-left mr-2 text-primary"></i>{{ __('About this competition') }}
                            </div>
                            <div class="card-body">
                                @if($competition->description)
                                    <div class="fp-desc">{!! nl2br(e($competition->description)) !!}</div>
                                @else
                                    <p class="fp-empty-hint mb-0">{{ __('No description has been added yet.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card fp-overview-card h-100">
                            <div class="card-header py-3">
                                <i class="fas fa-list-ul mr-2 text-primary"></i>{{ __('Key details') }}
                            </div>
                            <div class="card-body">
                                <dl class="mb-0 fp-dl-row">
                                    <dt>{{ __('Format') }}</dt>
                                    <dd>{{ $competition->type ?? '—' }}</dd>
                                    <dt>{{ __('Start') }}</dt>
                                    <dd>{{ isset($competition->start) ? date('M j, Y', (int) $competition->start) : '—' }}</dd>
                                    <dt>{{ __('End') }}</dt>
                                    <dd>{{ isset($competition->end) ? date('M j, Y', (int) $competition->end) : '—' }}</dd>
                                    <dt>{{ __('Max participants') }}</dt>
                                    <dd>{{ $competition->max_participants ?? '—' }}</dd>
                                    @if($competition->association)
                                        <dt>{{ __('Association') }}</dt>
                                        <dd>{{ $competition->association->name }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                @if($competition->pitch_image)
                    <div class="card fp-overview-card mt-3">
                        <div class="card-header py-3">
                            <i class="fas fa-map-marked-alt mr-2 text-primary"></i>{{ __('Pitch') }}
                        </div>
                        <div class="card-body p-0">
                            <img src="{{ asset($competition->pitch_image) }}" alt="" class="w-100" style="max-height: 220px; object-fit: cover;">
                        </div>
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="clubs" role="tabpanel" aria-labelledby="clubs-tab">
                <div class="card fp-clubs-card fp-overview-card">
                    <div class="card-header py-3 d-flex align-items-center justify-content-between flex-wrap">
                        <span>
                            <i class="fas fa-table mr-2 text-primary"></i>{{ __('League table') }}
                        </span>
                        <span class="badge badge-primary badge-pill">{{ $activeClubsCount }} {{ __('active') }}</span>
                    </div>
                    <div class="card-body">
                        @include('backend.layouts.partials.messages')
                        @if(empty($clubStandings))
                            <div class="fp-empty-hint">{{ __('No active clubs in this competition yet.') }}</div>
                        @else
                            <p class="fp-standings-hint mb-2">
                                {{ __('Standings from finished matches in this competition (status END or COMPLETED). 3 points for a win, 1 for a draw. Club column uses long name, with short code underneath when different.') }}
                            </p>
                            <div class="data-tables fp-standings-wrap">
                                <table id="dataTable" style="width:100%; min-width: 720px" class="text-center table table-sm table-hover mb-0 fp-standings-table">
                                    <thead class="bg-light text-capitalize">
                                        <tr>
                                            <th class="fp-num" width="4%">{{ __('#') }}</th>
                                            <th class="text-left">{{ __('Club') }}</th>
                                            <th class="fp-num" title="{{ __('Played') }}">P</th>
                                            <th class="fp-num" title="{{ __('Won') }}">W</th>
                                            <th class="fp-num" title="{{ __('Drawn') }}">D</th>
                                            <th class="fp-num" title="{{ __('Lost') }}">L</th>
                                            <th class="fp-num" title="{{ __('Goals for') }}">GF</th>
                                            <th class="fp-num" title="{{ __('Goals against') }}">GA</th>
                                            <th class="fp-num" title="{{ __('Goal difference') }}">GD</th>
                                            <th class="fp-num" title="{{ __('Points') }}">{{ __('Pts') }}</th>
                                            <th width="12%">{{ __('Entry') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($clubStandings as $idx => $row)
                                            <tr>
                                                <td class="fp-num font-weight-bold">{{ $idx + 1 }}</td>
                                                <td class="text-left fp-standings-club">
                                                    <a href="#"
                                                       class="fp-club-summary-trigger font-weight-bold"
                                                       role="button"
                                                       data-url="{{ route('admin.competition.club.summary', ['competition' => $competition->id, 'club' => $row['club']->id], false) }}"
                                                    >{{ $row['display_name'] }}</a>
                                                    @if($row['short_name'] !== $row['display_name'])
                                                        <div class="small text-muted">{{ $row['short_name'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="fp-num">{{ $row['played'] }}</td>
                                                <td class="fp-num">{{ $row['won'] }}</td>
                                                <td class="fp-num">{{ $row['drawn'] }}</td>
                                                <td class="fp-num">{{ $row['lost'] }}</td>
                                                <td class="fp-num">{{ $row['gf'] }}</td>
                                                <td class="fp-num">{{ $row['ga'] }}</td>
                                                <td class="fp-num">@if($row['gd'] > 0)+@endif{{ $row['gd'] }}</td>
                                                <td class="fp-num font-weight-bold">{{ $row['points'] }}</td>
                                                <td>
                                                    <span class="badge badge-success">{{ $row['pivot_status'] }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="invites" role="tabpanel" aria-labelledby="invites-tab">
                <div class="matchweek-filter">
                    <label for="matchweekSelect"><i class="fas fa-filter mr-1 text-muted"></i>{{ __('Matchweek') }}</label>
                    <select id="matchweekSelect" class="form-control form-control-sm">
                        <option value="">{{ __('All matchweeks') }}</option>
                        @foreach ($matchweeks as $mw)
                            <option value="{{ $mw }}" {{ (string) $currentMatchweek === (string) $mw ? 'selected' : '' }}>
                                {{ __('Matchweek') }} {{ $mw }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php
                    $matches = $currentMatchweek
                        ? $competition->matches()->where('matchweek', $currentMatchweek)->orderBy('date', 'asc')->get()
                        : $competition->matches()->orderBy('matchweek', 'asc')->orderBy('date', 'asc')->limit(50)->get();
                @endphp

                <div class="card fp-overview-card">
                    <div class="card-header py-3">
                        <i class="fas fa-calendar-alt mr-2 text-primary"></i>
                        {{ __('Fixtures') }}
                        @if($currentMatchweek)
                            <span class="badge badge-light text-dark border ml-2">{{ __('MW') }} {{ $currentMatchweek }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @include('backend.layouts.partials.messages')
                        @if($matches->isEmpty())
                            <div class="fp-empty-hint">{{ __('No matches found for this filter.') }}</div>
                        @else
                            <div class="data-tables">
                                <div class="d-none d-md-block">
                                    <table id="dataTable2" style="width:100%" class="text-center table table-hover mb-0">
                                        <thead class="bg-light text-capitalize">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="22%">{{ __('Home') }}</th>
                                                <th width="22%">{{ __('Away') }}</th>
                                                <th width="8%">{{ __('MW') }}</th>
                                                <th width="12%">{{ __('Date') }}</th>
                                                <th width="10%">{{ __('Status') }}</th>
                                                <th width="15%">{{ __('Actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($matches as $match)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="text-left">
                                                        {{ $match->home_club->name }} ({{ $match->home_score }})
                                                        @if ($usr->can('club.create'))
                                                            <br><a href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->home_club_id]) }}" class="btn btn-sm btn-outline-info mt-1">{{ __('Lineup') }}</a>
                                                        @endif
                                                    </td>
                                                    <td class="text-left">
                                                        ({{ $match->away_score }}) {{ $match->away_club->name }}
                                                        @if ($usr->can('club.create'))
                                                            <br><a href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->away_club_id]) }}" class="btn btn-sm btn-outline-info mt-1">{{ __('Lineup') }}</a>
                                                        @endif
                                                    </td>
                                                    <td>{{ $match->matchweek }}</td>
                                                    <td>{{ date('Y-m-d', $match->date) }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $match->status == 'COMPLETED' ? 'success' : ($match->status == 'LIVE' ? 'warning' : 'secondary') }}">
                                                            {{ $match->status }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.match.match_info', ['id' => $match->id]) }}" class="btn btn-sm btn-primary">{{ __('View') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-md-none">
                                    @foreach ($matches as $match)
                                        <div class="card fp-match-card mb-3">
                                            <div class="card-body">
                                                <div class="text-center">
                                                    <div class="fp-match-card__vs">VS</div>
                                                    <div class="text-muted small">{{ \Carbon\Carbon::createFromTimestamp($match->date)->format('M j, Y H:i') }}</div>
                                                    <div class="text-success small">{{ \Carbon\Carbon::createFromTimestamp($match->date)->diffForHumans() }}</div>
                                                    <div class="mt-1">
                                                        <span class="badge badge-info">MW{{ $match->matchweek }}</span>
                                                        <span class="badge badge-{{ $match->status == 'COMPLETED' ? 'success' : ($match->status == 'LIVE' ? 'warning' : 'secondary') }}">{{ $match->status }}</span>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mt-3">
                                                    <div class="col-5 text-center">
                                                        <div class="fp-club-mini__name">{{ Str::limit($match->home_club->name ?? '', 14) }}</div>
                                                    </div>
                                                    <div class="col-2 text-center">
                                                        <div class="text-muted small">vs</div>
                                                    </div>
                                                    <div class="col-5 text-center">
                                                        <div class="fp-club-mini__name">{{ Str::limit($match->away_club->name ?? '', 14) }}</div>
                                                    </div>
                                                </div>
                                                <div class="fp-match-actions mt-3">
                                                    @if ($usr->can('club.create'))
                                                        <a class="btn btn-outline-primary btn-block" href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->home_club_id]) }}">
                                                            <i class="fas fa-users"></i> {{ __('Home lineup') }}
                                                        </a>
                                                        <a class="btn btn-outline-primary btn-block" href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->away_club_id]) }}">
                                                            <i class="fas fa-users"></i> {{ __('Away lineup') }}
                                                        </a>
                                                    @endif
                                                    @if ($usr->can('match.edit'))
                                                        <a class="btn btn-outline-secondary btn-block" href="{{ route('admin.match.match_info', ['id' => $match->id]) }}">
                                                            <i class="fas fa-edit"></i> {{ __('Match update') }}
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clubSummaryModal" tabindex="-1" role="dialog" aria-labelledby="clubSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="clubSummaryModalLabel">{{ __('Club') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="clubSummaryModalBody" style="max-height: 70vh; overflow-y: auto;">
                    <p class="text-muted mb-0">{{ __('Loading…') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
<script>
    $(document).ready(function () {
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                responsive: true,
                paging: false,
                info: false,
                searching: false,
                ordering: false
            });
        }

        var table2;
        if ($('#dataTable2').length) {
            table2 = $('#dataTable2').DataTable({
                responsive: true,
                order: [[3, 'asc']],
                pageLength: 25
            });
        }

        $('#matchweekSelect').on('change', function () {
            var selectedMatchweek = $(this).val();
            var url = new URL(window.location.href);
            if (selectedMatchweek) {
                url.searchParams.set('matchweek', selectedMatchweek);
            } else {
                url.searchParams.delete('matchweek');
            }
            window.location.href = url.toString();
        });

        @if(request()->has('matchweek'))
        $('#invites-tab').tab('show');
        @endif

        function fpEsc(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function fpClubSummaryFetchUrl(url) {
            if (!url || window.location.protocol !== 'https:') {
                return url;
            }
            if (url.indexOf('http://') === 0) {
                try {
                    var u = new URL(url, window.location.href);
                    if (u.protocol === 'http:' && u.host === window.location.host) {
                        u.protocol = 'https:';
                        return u.href;
                    }
                } catch (e) {}
            }
            return url;
        }

        $(document).on('click', '.fp-club-summary-trigger', function (e) {
            e.preventDefault();
            var url = fpClubSummaryFetchUrl($(this).attr('data-url') || '');
            if (!url) {
                return;
            }
            var $modal = $('#clubSummaryModal');
            var $body = $('#clubSummaryModalBody');
            var $title = $('#clubSummaryModalLabel');
            $title.text('{{ __('Club') }}');
            $body.html('<p class="text-muted mb-0">{{ __('Loading…') }}</p>');
            $modal.modal('show');

            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            }).then(function (r) {
                return r.text().then(function (text) {
                    var data = {};
                    try {
                        data = text ? JSON.parse(text) : {};
                    } catch (err) {
                        data = { message: text ? text.replace(/<[^>]+>/g, ' ').trim().slice(0, 200) : 'Invalid response' };
                    }
                    return { ok: r.ok, status: r.status, data: data };
                });
            }).then(function (res) {
                if (!res.ok) {
                    $body.html('<p class="text-danger mb-0">' + fpEsc(res.data.message || '{{ __('Could not load club.') }}') + '</p>');
                    return;
                }
                var c = res.data.club || {};
                var players = res.data.players || [];
                $title.text(fpEsc(c.display_name || c.name || '{{ __('Club') }}'));

                var html = '';
                html += '<div class="row mb-3">';
                html += '<div class="col-sm-4 text-center mb-3 mb-sm-0">';
                html += '<img src="' + fpEsc(c.avatar_url) + '" alt="" class="rounded border" style="max-width:140px;max-height:140px;object-fit:cover;">';
                html += '</div>';
                html += '<div class="col-sm-8">';
                html += '<dl class="row mb-0 small">';
                if (c.name && c.name !== c.display_name) {
                    html += '<dt class="col-sm-4 text-muted">{{ __('Code') }}</dt><dd class="col-sm-8 font-weight-bold">' + fpEsc(c.name) + '</dd>';
                }
                if (c.long_name) {
                    html += '<dt class="col-sm-4 text-muted">{{ __('Long name') }}</dt><dd class="col-sm-8">' + fpEsc(c.long_name) + '</dd>';
                }
                if (c.association) {
                    html += '<dt class="col-sm-4 text-muted">{{ __('Association') }}</dt><dd class="col-sm-8">' + fpEsc(c.association) + '</dd>';
                }
                html += '<dt class="col-sm-4 text-muted">{{ __('Status') }}</dt><dd class="col-sm-8"><span class="badge badge-secondary">' + fpEsc(c.status || '—') + '</span></dd>';
                html += '</dl>';
                html += '</div></div>';

                html += '<h6 class="font-weight-bold border-bottom pb-2 mb-2">{{ __('Players') }} <span class="badge badge-light text-dark border">' + players.length + '</span></h6>';
                if (players.length === 0) {
                    html += '<p class="text-muted small mb-0">{{ __('No players linked to this club yet.') }}</p>';
                } else {
                    html += '<div class="table-responsive"><table class="table table-sm table-striped mb-0 fp-club-players-table"><thead class="thead-light"><tr>';
                    html += '<th class="fp-club-player-photo-col" aria-label="{{ __('Photo') }}"></th><th class="text-left">{{ __('Name') }}</th><th>{{ __('Pos') }}</th><th>{{ __('#') }}</th><th>{{ __('Status') }}</th>';
                    html += '</tr></thead><tbody>';
                    for (var i = 0; i < players.length; i++) {
                        var p = players[i];
                        var av = p.avatar_url || '';
                        html += '<tr>';
                        html += '<td class="align-middle text-center fp-club-player-photo-col">';
                        html += '<img src="' + fpEsc(av) + '" alt="" class="rounded-circle border fp-club-player-thumb" width="40" height="40">';
                        html += '</td>';
                        html += '<td class="text-left font-weight-bold align-middle">' + fpEsc(p.name) + '</td>';
                        html += '<td class="align-middle">' + fpEsc(p.position || '—') + '</td>';
                        html += '<td class="align-middle">' + fpEsc(p.jersey != null && p.jersey !== '' ? p.jersey : '—') + '</td>';
                        html += '<td class="align-middle"><span class="badge badge-info">' + fpEsc(p.status || '—') + '</span></td>';
                        html += '</tr>';
                    }
                    html += '</tbody></table></div>';
                }
                $body.html(html);
            }).catch(function () {
                $body.html('<p class="text-danger mb-0">{{ __('Connection failed.') }}</p>');
            });
        });
    });
</script>
@endsection
