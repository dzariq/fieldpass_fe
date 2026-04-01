@extends('backend.layouts.master')

@section('title')
Match Edit - Admin Panel
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
                <h4 class="page-title pull-left">Match Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.matches.index') }}">All Matches</a></li>
                    <li><span>Edit Match - {{ $match->name }}</span></li>
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
                    <h4 class="header-title">Edit Match - {{ $match->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.matches.update', $match->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-4 col-sm-12">
                                <label for="date">Match Date</label>
                                <input type="date" name="date" value="{{ date('Y-m-d', $match->date) }}" class="form-control" required />
                            </div>
                            <div class="form-group col-md-2 col-sm-12">
                                <label for="time">Match Time</label>
                                <input type="time" name="time" value="{{ date('H:i', $match->date) }}" class="form-control" required />
                            </div>
                        </div>

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" class="form-control" name="competition_id">
                                    @foreach ($associations as $association)
                                    <option value="{{ $association->id }}"
                                        {{ old('association_id', $match->association_id) == $association->id ? 'selected' : '' }}>
                                        {{ $association->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Matchweek</label>
                                <input type="number" name="matchweek" class="form-control" value="{{ $match->matchweek }}" />
                            </div>
                        </div>
                        @elseif(count($adminObj->associations) > 0)
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" class="form-control" name="competition_id">
                                    @foreach ($adminObj->associations as $association)
                                        @foreach ($association->competitions as $competition)
                                            <option value="{{ $competition->id }}"
                                                {{ old('competition_id', $match->competition_id) == $competition->id ? 'selected' : '' }}>
                                                {{ $competition->name }}
                                            </option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Matchweek</label>
                                <input type="number" name="matchweek" class="form-control" value="{{ $match->matchweek }}" />
                            </div>
                        </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Home Club</label>
                                <input type="hidden" id="home_club_id_default" value="{{ $match->home_club_id }}" />
                                <input type="hidden" id="away_club_id_default" value="{{ $match->away_club_id }}" />
                                
                                <select onchange="validateClubs()" required class="form-control" name="home_club_id" id="home_club_id">
                                    <option value="">Select Club</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Score</label>
                                <input type="number" name="home_score" class="form-control" value="{{ $match->home_score }}" />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Away Club</label>
                                <select onchange="validateClubs()" required class="form-control" name="away_club_id" id="away_club_id">
                                    <option value="">Select Club</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Score</label>
                                <input type="number" name="away_score" class="form-control" value="{{ $match->away_score }}" />
                            </div>
                        </div>
                        <button id="submit_match" type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.matches.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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
    })
</script>
@endsection