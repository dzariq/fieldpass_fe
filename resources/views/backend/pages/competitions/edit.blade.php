@extends('backend.layouts.master')

@section('title')
Competition Edit - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .image-preview {
        max-width: 100%;
        height: auto;
        margin-top: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
    }
    .reinvite-section {
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    .reinvite-section h5 {
        color: #856404;
        margin-bottom: 15px;
    }
    .custom-checkbox-large {
        transform: scale(1.2);
        margin-right: 10px;
    }
</style>
@endsection

@php
$usr = Auth::guard('admin')->user();
$adminObj = App\Models\Admin::find($usr->id);

// Get clubs with INVITED status (not accepted/rejected)
$invitedClubs = $competition->clubs->filter(function($club) {
    return $club->pivot->status === 'INVITED';
});
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
                    <h4 class="header-title">Edit Competition - {{ $competition->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form id="{{ $competition->id }}-competition-update-form" action="{{ route('admin.competitions.update', $competition->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf

                        <div class="form-group text-center">
                            <label for="avatar">Avatar</label><br>
                            <img src="{{ $competition->avatar ? asset($competition->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                alt="Avatar"
                                class="rounded-circle mb-3"
                                width="150" height="150">

                            <input type="file" class="form-control-file mt-2" name="avatar" id="avatar" accept="image/*">
                            <small class="form-text text-muted">Max size: 2MB</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="banner">Competition Banner</label>
                                @if($competition->banner)
                                    <img src="{{ asset($competition->banner) }}" alt="Current Banner" class="image-preview">
                                @endif
                                <input type="file" class="form-control-file mt-2" name="banner" id="banner" accept="image/jpeg,image/png,image/jpg">
                                <small class="form-text text-muted">
                                    Recommended size: 1200 × 300 px. Upload JPG or PNG file. Max size: 2MB
                                </small>
                            </div>

                            <div class="form-group col-md-6 col-sm-12">
                                <label for="pitch_image">Pitch Image</label>
                                <input type="hidden" name="clear_pitch_image" id="clear_pitch_image_flag" value="0">
                                <div id="pitch-image-preview-wrap">
                                    @if($competition->pitch_image)
                                        <img src="{{ asset($competition->pitch_image) }}" alt="Current Pitch" class="image-preview">
                                    @endif
                                </div>
                                <input type="file" class="form-control-file mt-2" name="pitch_image" id="pitch_image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                @if($competition->pitch_image)
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2 d-block" id="btn-clear-pitch-image">
                                        Remove current image
                                    </button>
                                @endif
                                <small class="form-text text-muted">Upload JPG or PNG file. Max size: 2MB</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="pitch_board_1">Pitch board 1</label>
                                @if(!empty($competition->pitch_board_1))
                                    <img src="{{ asset($competition->pitch_board_1) }}" alt="Current pitch board 1" class="image-preview">
                                @endif
                                <input type="file" class="form-control-file mt-2" name="pitch_board_1" id="pitch_board_1" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="form-text text-muted">
                                    Recommended size: 200 × 100 px. Upload JPG or PNG file. Max size: 2MB
                                </small>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="pitch_board_2">Pitch board 2</label>
                                @if(!empty($competition->pitch_board_2))
                                    <img src="{{ asset($competition->pitch_board_2) }}" alt="Current pitch board 2" class="image-preview">
                                @endif
                                <input type="file" class="form-control-file mt-2" name="pitch_board_2" id="pitch_board_2" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="form-text text-muted">
                                    Recommended size: 200 × 100 px. Upload JPG or PNG file. Max size: 2MB
                                </small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ $competition->name }}" required autofocus>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="type">Type</label>
                                <select disabled="true" class="form-control" name="type">
                                    <option value="league">League</option>
                                    <option value="tournament">Tournament</option>
                                    <option value="cup">Cup</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start" required value="{{ date('Y-m-d',$competition->start) }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end" required value="{{ date('Y-m-d',$competition->end) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="max_participants">Max Participants</label>
                                <input readonly="true" type="number" name="max_participants" class="form-control" value="{{$competition->max_participants}}" />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="description">Description</label>
                                <textarea name="description" class="form-control">{{$competition->description}}</textarea>
                            </div>
                        </div>

                        <input type="hidden" name="association_id" value="{{$competition->association_id}}" />

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="association">Association</label>
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
                                <label for="association">Association</label>
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
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="clubs">Invite Clubs</label>
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
                             <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition Fee (RM)</label>
                                <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', $competition->price ?? 0) }}" required />
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="ACTIVE" {{ old('status', $competition->status ?? 'ACTIVE') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                    <option value="INACTIVE" {{ old('status', $competition->status ?? 'ACTIVE') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                <small class="form-text text-muted">Inactive competitions can be hidden from selection/lists.</small>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary pr-4 pl-4">
                                <i class="fa fa-save"></i> Save Competition
                            </button>
                            <a href="{{ route('admin.competitions.index') }}" class="btn btn-secondary pr-4 pl-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>

                    @if($invitedClubs->count() > 0)
                    <!-- Re-invite Section (part of update form via checkbox) -->
                    <div class="reinvite-section">
                        <h5><i class="fa fa-envelope"></i> Re-send Invitations</h5>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input custom-checkbox-large" id="reinvite_pending" name="reinvite_pending" value="1" form="{{ $competition->id }}-competition-update-form">
                            <label class="custom-control-label" for="reinvite_pending">
                                <strong>Re-invite all pending clubs ({{ $invitedClubs->count() }} clubs)</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fa fa-info-circle"></i> This will resend WhatsApp invitations to all clubs that haven't accepted or rejected the invitation yet.
                            <br>
                            Pending clubs:
                            @foreach($invitedClubs as $club)
                                <span class="badge badge-warning">{{ $club->name }}</span>
                            @endforeach
                        </small>
                    </div>

                    <!-- Force Join MUST be a separate form (no nested forms) -->
                    <div class="reinvite-section mt-3">
                        <h5><i class="fa fa-check-circle"></i> Force Join Pending Clubs</h5>
                        <small class="form-text text-muted mb-2">
                            This will immediately activate the selected pending clubs (INVITED → ACTIVE) without waiting for club acceptance.
                        </small>

                        <form action="{{ route('admin.competition.forceJoin', $competition->id) }}" method="POST" class="border rounded p-3">
                            @csrf
                            <div class="mb-2">
                                @foreach($invitedClubs as $club)
                                    <label class="mr-3 mb-2" style="display:inline-block;">
                                        <input type="checkbox" name="club_ids[]" value="{{ $club->id }}"> {{ $club->name }}
                                    </label>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-bolt"></i> Force Join Selected
                            </button>
                        </form>
                    </div>
                    @endif
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

        $('#btn-clear-pitch-image').on('click', function() {
            $('#clear_pitch_image_flag').val('1');
            $('#pitch-image-preview-wrap').hide();
            $('#pitch_image').val('');
        });
    });
</script>
@endsection