@extends('playerbackend.layouts.master')

@section('title')
{{ __('Match - Admin Panel') }}
@endsection

@section('styles')
<!-- Start datatable css -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
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
            @include('playerbackend.layouts.partials.logout')
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
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <select id="matchweekFilter" class="form-control">
                                    <option value="">{{ __('All Matchweeks') }}</option>
                                    @foreach ($matches->pluck('matchweek')->unique()->sort() as $week)
                                    <option value="MW {{ $week }}">{{ __('MW') }} {{ $week }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <select id="competitionFilter" class="form-control">
                                    <option value="">{{ __('All Competitions') }}</option>
                                    @foreach ($matches->pluck('competition.name')->unique()->sort() as $competition)
                                    <option value="{{ $competition }}">{{ $competition }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @include('playerbackend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
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
                                @foreach ($matches as $match)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
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

                                        @if (auth()->user()->can('match.details'))
                                        <a class="btn btn-success text-white" href="{{ route('admin.matches.details', $match->id) }}">View</a>
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
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
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
    $(document).ready(function () {
        let table = $('#dataTable').DataTable({
            responsive: true
        });

        // Function to apply both filters
        function applyFilters() {
            let matchweek = $('#matchweekFilter').val();
            let competition = $('#competitionFilter').val();

            table
                .columns(4).search(matchweek)  // Column 4 = Matchweek
                .columns(3).search(competition) // Column 3 = Competition
                .draw();
        }

        // Event listeners for dropdown changes
        $('#matchweekFilter, #competitionFilter').on('change', function () {
            applyFilters();
        });
    });
</script>

@endsection