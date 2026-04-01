@extends('backend.layouts.master')

@section('title')
Match Create - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
</style>
@endsection

@php
$usr = Auth::guard('admin')->user();
$adminObj = App\Models\Admin::with('associations.competitions')->find($usr->id);
@endphp

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Match Create</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.matches.index') }}">All Matches</a></li>
                    <li><span>Create Match</span></li>
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
                    <h4 class="header-title">Create New Match</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.matches.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label for="date">Match Date</label>
                                <input type="date" 
                                       name="date" 
                                       class="form-control" 
                                       value="{{ old('date', date('Y-m-d')) }}" 
                                       required />
                            </div>
                            <div class="form-group col-md-2 col-sm-12">
                                <label for="time">Match Time</label>
                                <input type="time" 
                                       name="time" 
                                       class="form-control" 
                                       value="{{ old('time', '20:00') }}" 
                                       required />
                            </div>
                        </div>

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="competition">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" 
                                        class="form-control" 
                                        name="competition_id" 
                                        required>
                                    <option value="">Select Competition</option>
                                    @foreach ($competitions as $competition)
                                    <option value="{{ $competition->id }}" 
                                            {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
                                        {{ $competition->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="matchweek">Matchweek</label>
                                <input type="number" 
                                       name="matchweek" 
                                       class="form-control" 
                                       value="{{ old('matchweek', 1) }}" 
                                       min="1" 
                                       required />
                            </div>
                        </div>

                        @elseif(count($adminObj->associations) > 0)
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="competition">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" 
                                        class="form-control" 
                                        name="competition_id" 
                                        required>
                                    <option value="">Select Competition</option>
                                    @foreach ($adminObj->associations as $association)
                                        @foreach ($association->competitions as $competition)
                                            <option value="{{ $competition->id }}" 
                                                    {{ old('competition_id') == $competition->id ? 'selected' : '' }}>
                                                {{ $competition->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="matchweek">Matchweek</label>
                                <input type="number" 
                                       name="matchweek" 
                                       class="form-control" 
                                       value="{{ old('matchweek', 1) }}" 
                                       min="1" 
                                       required />
                            </div>    
                        </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="home_club">Home Club</label>
                                <select onchange="validateClubs()" 
                                        required 
                                        class="form-control" 
                                        name="home_club_id" 
                                        id="home_club_id">
                                    <option value="">Select Club</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="home_score">Home Score</label>
                                <input type="number" 
                                       name="home_score" 
                                       class="form-control" 
                                       value="{{ old('home_score', 0) }}" 
                                       min="0" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="away_club">Away Club</label>
                                <select onchange="validateClubs()" 
                                        required 
                                        class="form-control" 
                                        name="away_club_id" 
                                        id="away_club_id">
                                    <option value="">Select Club</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="away_score">Away Score</label>
                                <input type="number" 
                                       name="away_score" 
                                       class="form-control" 
                                       value="{{ old('away_score', 0) }}" 
                                       min="0" />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> 
                                    <strong>Match Preview:</strong> 
                                    <span id="matchPreview">Select clubs and set date/time</span>
                                </div>
                            </div>
                        </div>

                        <button id="submit_match" type="submit" class="btn btn-primary mt-4 pr-4 pl-4">
                            <i class="fa fa-save"></i> Save Match
                        </button>
                        <a href="{{ route('admin.matches.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </form>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        
        // Update match preview
        function updateMatchPreview() {
            const homeClub = $('#home_club_id option:selected').text();
            const awayClub = $('#away_club_id option:selected').text();
            const date = $('input[name="date"]').val();
            const time = $('input[name="time"]').val();
            
            if (homeClub && awayClub && homeClub !== 'Select Club' && awayClub !== 'Select Club' && date && time) {
                const formattedDate = new Date(date).toLocaleDateString('en-GB', { 
                    day: '2-digit', 
                    month: 'short', 
                    year: 'numeric' 
                });
                $('#matchPreview').html(`<strong>${homeClub}</strong> vs <strong>${awayClub}</strong> - ${formattedDate} at ${time}`);
            }
        }
        
        // Trigger preview update on change
        $('#home_club_id, #away_club_id, input[name="date"], input[name="time"]').on('change', updateMatchPreview);
    });
</script>
@endsection