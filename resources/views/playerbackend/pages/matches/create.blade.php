@extends('playerbackend.layouts.master')

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
$usr = Auth::guard('player')->user();
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
                    <h4 class="header-title">Create New Match</h4>
                    @include('playerbackend.layouts.partials.messages')

                    <form action="{{ route('admin.matches.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Match Date</label>
                                <input type="date" name="date" class="form-control" required />
                            </div>
                        </div>

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" class="form-control" name="competition_id">
                                    @foreach ($competitions as $competition)
                                    <option value="{{ $competition->id }}">{{ $competition->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Matchweek</label>
                                <input type="number" name="matchweek" class="form-control" value="1" />
                            </div>

                        </div>

                        @elseif(count($adminObj->associations) > 0)
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition</label>
                                <select onchange="getClubsByCompetition(this.value)" class="form-control" name="competition_id">
                                    @foreach ($adminObj->associations as $association)
                                        @foreach ($association->competitions as $competition)
                                            <option value="{{ $competition->id }}">{{ $competition->name }}</option>
                                        @endforeach
                                    @endforeach
                                 </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Matchweek</label>
                                <input type="number" name="matchweek" class="form-control" value="1" required />
                            </div>    
                        </div>

                        @endif
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Home Club</label>
                                <select onchange="validateClubs()" required class="form-control" name="home_club_id" id="home_club_id">
                                    <option value="">Select Club</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Score</label>
                                <input type="number" name="home_score" class="form-control" value="0" />
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
                                <input type="number" name="away_score" class="form-control" value="0" />
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