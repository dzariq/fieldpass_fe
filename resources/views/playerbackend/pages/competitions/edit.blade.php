@extends('playerbackend.layouts.master')

@section('title')
Competition Edit - Admin Panel
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
$adminObj = App\Models\Admin::find($usr->id);
@endphp

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Competition Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.competitions.index') }}">All Competitions</a></li>
                    <li><span>Edit Competition - {{ $competition->name }}</span></li>
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
                    <h4 class="header-title">Edit Competition - {{ $competition->name }}</h4>
                    @include('playerbackend.layouts.partials.messages')

                    <form action="{{ route('admin.competitions.update', $competition->id) }}" method="POST">
                        @method('PUT')
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ $competition->name }}" required autofocus>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Type</label>
                                <select disabled="true" class="form-control" name="type" >
                                    <option value="league">League</option>
                                    <option value="tournament">Tournament</option>
                                    <option value="cup">Cup</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start" required autofocus value="{{ date('Y-m-d',$competition->start) }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end" required autofocus value="{{ date('Y-m-d',$competition->end) }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Max Participants</label>
                                <input readonly="true" type="number" name="max_participants" class="form-control" value="{{$competition->max_participants}}" />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Description</label>
                                <textarea name="description" class="form-control">{{$competition->description}}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="association_id" value="{{$competition->association_id}}" />

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association</label>
                                <select disabled class="form-control" name="association_id">
                                    @foreach ($associations as $association)
                                    <option value="{{ $association->id }}"
                                        {{ old('association_id', $competition->association_id) == $association->id ? 'selected' : '' }}>
                                        {{ $association->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @elseif(count($adminObj->associations) > 0)
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association</label>
                                <select disabled class="form-control" name="association_id">
                                    @foreach ($adminObj->associations as $association)
                                    <option value="{{ $association->id }}"
                                        {{ old('association_id', $competition->association_id) == $association->id ? 'selected' : '' }}>
                                        {{ $association->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="password">Invite Clubs</label>
                                <select name="club_ids[]" id="clubs" class="form-control select2" multiple>
                                    @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}"
                                        {{ $competition->clubs->contains(function ($c) use ($club) {
            return $c->id === $club->id && in_array($c->pivot->status, ['INVITED', 'ACTIVE']);
        }) ? 'selected' : '' }}>
                                        {{ $club->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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