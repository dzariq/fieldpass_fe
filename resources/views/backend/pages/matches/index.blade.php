@extends('backend.layouts.master')

@section('title')
{{ __('Match - Admin Panel') }}
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ __('Matches') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('All Matches') }}</span></li>
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
    <div class="row">
        <!-- data table start -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">{{ __('Matches') }}</h4>
                    <p class="float-right mb-2">
                        @if (auth()->user()->can('match.edit'))
                        <a class="btn btn-primary text-white" href="{{ route('admin.matches.create') }}">
                            {{ __('Create New Match') }}
                        </a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        <form method="get" action="{{ route('admin.matches.index') }}" class="row mb-2 align-items-end">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="small text-muted d-block mb-1">{{ __('Matchweek') }}</label>
                                <select name="matchweek" class="form-control form-control-sm">
                                    <option value="">{{ __('All Matchweeks') }}</option>
                                    @foreach ($filterMatchweeks as $week)
                                    <option value="{{ $week }}" {{ request('matchweek') == $week ? 'selected' : '' }}>{{ __('MW') }} {{ $week }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="small text-muted d-block mb-1">{{ __('Competition') }}</label>
                                <select name="competition_id" class="form-control form-control-sm">
                                    <option value="">{{ __('All Competitions') }}</option>
                                    @foreach ($filterCompetitions as $comp)
                                    <option value="{{ $comp->id }}" {{ request('competition_id') == (string)$comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">{{ __('Filter') }}</button>
                                @if(request()->hasAny(['matchweek', 'competition_id']))
                                <a href="{{ route('admin.matches.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Reset') }}</a>
                                @endif
                            </div>
                        </form>
                        @include('backend.layouts.partials.messages')
                        <!-- Desktop/tablet table -->
                        <div class="d-none d-md-block">
                            <table class="table table-hover text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th width="5%">{{ __('Sl') }}</th>
                                    <th width="10%">{{ __('Home Club') }}</th>
                                    <th width="10%">{{ __('Away Club') }}</th>
                                    <th width="15%">{{ __('Competition') }}</th>
                                    <th width="5%">{{ __('Matchweek') }}</th>
                                    <th width="5%">{{ __('Date') }}</th>
                                    <th width="15%">{{ __('Status') }}</th>
                                    <th width="15%">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($matches as $match)
                                <tr>
                                    <td>{{ ($matches->firstItem() ?? 0) + $loop->index }}</td>
                                    <td>{{ $match->home_club->name }} ({{ $match->home_score }})</td>
                                    <td>{{ $match->away_club->name }} ({{ $match->away_score }})</td>
                                    <td>{{ $match->competition ? $match->competition->name : '' }}</td>
                                    <td>MW {{ $match->matchweek}}</td>
                                    <td>{{ date('F j, Y',$match->date) }}</td>
                                    <td>
                                        <span class="badge badge-info mr-1">
                                            {{ $match->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (auth()->user()->can('match.edit'))
                                        <a class="btn btn-success text-white" href="{{ route('admin.matches.edit', $match->id) }}">Edit</a>
                                        @endif

                                     

                                        @if (auth()->user()->can('match.delete'))
                                        <a class="btn btn-danger text-white" href="javascript:void(0);"
                                            onclick="event.preventDefault(); if(confirm('Are you sure you want to delete?')) { document.getElementById('delete-form-{{ $match->id }}').submit(); }">
                                            {{ __('Delete') }}
                                        </a>

                                        <form id="delete-form-{{ $match->id }}" action="{{ route('admin.matches.destroy', $match->id) }}" method="POST" style="display: none;">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-muted py-4">{{ __('No matches found.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                            </table>
                        </div>

                        <!-- Mobile cards (match the fixture look) -->
                        <div class="d-md-none">
                            @forelse ($matches as $match)
                                <div class="card mb-3 match-card">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <div class="match-card__vs">VS</div>
                                            <div class="text-muted small">{{ \Carbon\Carbon::createFromTimestamp($match->date)->format('M j, Y H:i') }}</div>
                                            <div class="text-success small">{{ \Carbon\Carbon::createFromTimestamp($match->date)->diffForHumans() }}</div>
                                            <div class="mt-1">
                                                <span class="badge badge-info">MW{{ $match->matchweek }}</span>
                                                @if($match->competition)
                                                    <span class="badge badge-outline-success">{{ $match->competition->type }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row align-items-center mt-3">
                                            <div class="col-5 text-center">
                                                <div class="club-mini">
                                                    <div class="club-mini__name">{{ Str::limit($match->home_club->name ?? '', 14) }}</div>
                                                    @if(isset($match->home_lineup_submitted) && !(int)$match->home_lineup_submitted)
                                                        <span class="badge badge-danger badge-sm mt-1">Missing</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-2 text-center">
                                                <div class="text-muted small">vs</div>
                                            </div>
                                            <div class="col-5 text-center">
                                                <div class="club-mini">
                                                    <div class="club-mini__name">{{ Str::limit($match->away_club->name ?? '', 14) }}</div>
                                                    @if(isset($match->away_lineup_submitted) && !(int)$match->away_lineup_submitted)
                                                        <span class="badge badge-danger badge-sm mt-1">Missing</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="match-actions mt-3">
                                            @can('admin.view')
                                                <a class="btn btn-outline-primary btn-block"
                                                   href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->home_club_id]) }}">
                                                    <i class="fas fa-users"></i> Home Lineup
                                                </a>
                                                <a class="btn btn-outline-primary btn-block"
                                                   href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->away_club_id]) }}">
                                                    <i class="fas fa-users"></i> Away Lineup
                                                </a>
                                            @endcan
                                            @can('match.edit')
                                                <a class="btn btn-outline-secondary btn-block"
                                                   href="{{ route('admin.match.match_info', ['id' => $match->id]) }}">
                                                    <i class="fas fa-edit"></i> Match Update
                                                </a>
                                            @endcan

                                            @can('admin.view')
                                                @if(isset($match->home_lineup_submitted) && !(int)$match->home_lineup_submitted)
                                                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small text-center">
                                                        <i class="fas fa-exclamation-triangle"></i> Home club lineup not submitted
                                                    </div>
                                                @endif
                                                @if(isset($match->away_lineup_submitted) && !(int)$match->away_lineup_submitted)
                                                    <div class="alert alert-warning py-1 px-2 mt-2 mb-0 small text-center">
                                                        <i class="fas fa-exclamation-triangle"></i> Away club lineup not submitted
                                                    </div>
                                                @endif
                                            @endcan
                                        </div>

                                        <div class="text-center mt-3">
                                            <span class="badge badge-info">{{ $match->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted py-4 text-center">{{ __('No matches found.') }}</div>
                            @endforelse
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">
                            <div class="small text-muted">
                                {{ __('Showing') }} {{ $matches->firstItem() ?? 0 }} {{ __('to') }} {{ $matches->lastItem() ?? 0 }} {{ __('of') }} {{ $matches->total() }} {{ __('matches') }}
                            </div>
                            <div>
                                {{ $matches->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('styles')
<style>
    .badge-outline-success {
        color: #28a745;
        border: 1px solid #28a745;
        background-color: rgba(40, 167, 69, 0.1);
    }
    .match-card {
        border-radius: 14px;
        border: 1px solid rgba(16, 24, 40, 0.10);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }
    .match-card__vs {
        font-weight: 800;
        font-size: 1.1rem;
        color: #334155;
        letter-spacing: -0.01em;
    }
    .club-mini__name {
        font-weight: 800;
        color: #0f172a;
        font-size: 0.95rem;
        line-height: 1.1;
    }
    .match-actions .btn {
        border-radius: 10px;
        padding: 10px 12px;
        font-weight: 800;
        box-shadow: none;
    }
    .match-actions .btn-outline-primary {
        background: rgba(37, 99, 235, 0.06);
        border-color: rgba(37, 99, 235, 0.35);
        color: #1d4ed8;
    }
    .match-actions .btn-outline-secondary {
        background: rgba(100, 116, 139, 0.08);
        border-color: rgba(100, 116, 139, 0.35);
        color: #334155;
    }
</style>
@endsection