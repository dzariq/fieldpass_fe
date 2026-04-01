@extends('backend.layouts.master')

@section('title')
{{ __('Competition Detail - Admin Panel') }}
@endsection

@section('styles')
<!-- Start datatable css -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
<style>
    .matchweek-filter {
        margin-bottom: 20px;
    }
    .matchweek-filter label {
        font-weight: 600;
        margin-right: 10px;
    }
    .matchweek-filter select {
        padding: 8px 15px;
        border-radius: 4px;
        border: 1px solid #ddd;
        min-width: 150px;
    }
</style>
@endsection

@section('admin-content')
@php
$usr = Auth::guard('admin')->user();
// Get all unique matchweeks from matches
$matchweeks = $competition->matches()->select('matchweek')->distinct()->orderBy('matchweek', 'asc')->pluck('matchweek');
$currentMatchweek = request()->get('matchweek', $matchweeks->first());
@endphp

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ $competition->name }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a></li>
                    <li><span>{{ __('Competition Details') }}</span></li>
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
    <div class="container mt-4">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" id="competitionTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="overview-tab" data-toggle="tab" href="#overview" role="tab" aria-controls="overview" aria-selected="true">Overview</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="clubs-tab" data-toggle="tab" href="#clubs" role="tab" aria-controls="clubs" aria-selected="false">Clubs</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="invites-tab" data-toggle="tab" href="#invites" role="tab" aria-controls="invites" aria-selected="false">Matches</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content mt-3" id="competitionTabsContent">
            <div class="tab-pane fade show active" id="overview" role="tabpanel" aria-labelledby="overview-tab">
                <h3>Overview</h3>
                <img
                    src="{{ $competition->avatar ? asset($competition->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                    style="height: 60px; width: 60px; object-fit: cover; border-radius: 10px;"
                    alt="{{ $competition->name }} Logo" />
                <p>{{ $competition->description }}</p>
            </div>
            <div class="tab-pane fade" id="clubs" role="tabpanel" aria-labelledby="clubs-tab">
                <div class="col-12 mt-5">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title float-left">{{ __('Clubs') }}</h4>
                            <div class="clearfix"></div>
                            <div class="data-tables">
                                @include('backend.layouts.partials.messages')
                                <table id="dataTable" style="width:100%" class="text-center">
                                    <thead class="bg-light text-capitalize">
                                        <tr>
                                            <th width="5%">{{ __('ID') }}</th>
                                            <th width="10%">{{ __('Club Name') }}</th>
                                            <th width="10%">{{ __('Status') }}</th>
                                            <th width="10%">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($competition->clubs()->get() as $club)
                                        @if($club->pivot->status == 'ACTIVE')
                                        <tr>
                                            <td>{{ $loop->index+1 }}</td>
                                            <td>{{ $club->name }}</td>
                                            <td>{{ $club->pivot->status }}</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="invites" role="tabpanel" aria-labelledby="invites-tab">
                <h3>Matches</h3>
                
                <!-- Matchweek Filter -->
                <div class="matchweek-filter">
                    <label for="matchweekSelect">{{ __('Filter by Matchweek:') }}</label>
                    <select id="matchweekSelect" class="form-control" style="display: inline-block; width: auto;">
                        <option value="">{{ __('All Matchweeks') }}</option>
                        @foreach ($matchweeks as $mw)
                        <option value="{{ $mw }}" {{ $currentMatchweek == $mw ? 'selected' : '' }}>
                            {{ __('Matchweek') }} {{ $mw }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="header-title float-left">
                                {{ __('Matches') }}
                                @if($currentMatchweek)
                                    - {{ __('Matchweek') }} {{ $currentMatchweek }}
                                @endif
                            </h4>
                            <div class="clearfix"></div>
                            <div class="data-tables">
                                @include('backend.layouts.partials.messages')
                                <table id="dataTable2" style="width:100%" class="text-center">
                                    <thead class="bg-light text-capitalize">
                                        <tr>
                                            <th width="5%">{{ __('ID') }}</th>
                                            <th width="20%">{{ __('Home Club') }}</th>
                                            <th width="20%">{{ __('Away Club') }}</th>
                                            <th width="10%">{{ __('MW') }}</th>
                                            <th width="10%">{{ __('Date') }}</th>
                                            <th width="10%">{{ __('Status') }}</th>
                                            <th width="15%">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                        $matches = $currentMatchweek 
                                            ? $competition->matches()->where('matchweek', $currentMatchweek)->orderBy('date', 'asc')->get()
                                            : $competition->matches()->orderBy('matchweek', 'asc')->orderBy('date', 'asc')->limit(50)->get();
                                        @endphp
                                        
                                        @foreach ($matches as $match)
                                        <tr>
                                            <td>{{ $loop->index+1 }}</td>
                                            <td>
                                                {{ $match->home_club->name }} ({{ $match->home_score }})
                                                @if ($usr->can('club.create'))
                                                    <br><a href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->home_club_id]) }}" class="btn btn-sm btn-info mt-1">Lineup</a>
                                                @endif
                                            </td>
                                            <td>
                                                ({{ $match->away_score }}) {{ $match->away_club->name }}
                                                @if ($usr->can('club.create'))
                                                    <br><a href="{{ route('admin.player.lineup', ['id' => $match->id, 'club_id' => $match->away_club_id]) }}" class="btn btn-sm btn-info mt-1">Lineup</a>
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
                                                <a href="{{ route('admin.match.match_info', ['id' => $match->id]) }}" class="btn btn-sm btn-primary">
                                                    {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Start datatable js -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($('#dataTable').length) {
            $('#dataTable').DataTable({
                responsive: true
            });
        }
        
        var table2;
        if ($('#dataTable2').length) {
            table2 = $('#dataTable2').DataTable({
                responsive: true,
                order: [[3, 'asc']], // Sort by matchweek column
                pageLength: 25
            });
        }

        // Matchweek filter change handler
        $('#matchweekSelect').on('change', function() {
            var selectedMatchweek = $(this).val();
            var url = new URL(window.location.href);
            
            if (selectedMatchweek) {
                url.searchParams.set('matchweek', selectedMatchweek);
            } else {
                url.searchParams.delete('matchweek');
            }
            
            // Reload page with new matchweek parameter
            window.location.href = url.toString();
        });

        // Activate the matches tab if matchweek parameter is present
        @if(request()->has('matchweek'))
        $('#invites-tab').tab('show');
        @endif
    });
</script>
@endsection