@extends('backend.layouts.master')

@section('title')
Competition Create - Admin Panel
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
$adminObj = App\Models\Admin::find($usr->id);
@endphp

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Competition Create</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.competitions.index') }}">All competitions</a></li>
                    <li><span>Create competition</span></li>
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
                    <h4 class="header-title">Create New Competition</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.competitions.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required autofocus value="{{ old('name') }}"  enctype="multipart/form-data">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="avatar">Avatar</label><br>
                                <img src="{{ asset('backend/assets/images/default-avatar.png') }}"
                                    alt="Avatar"
                                    class="rounded-circle mb-3"
                                    width="150" height="150">

                                <input type="file" class="form-control-file mt-2" name="avatar" id="avatar" accept="image/*">
                                <small class="form-text text-muted">Max size: 2MB</small>
                            </div>

                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="league">League</option>
                                    <option value="tournament">Tournament</option>
                                    <option value="cup">Cup</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start" required autofocus value="{{ old('start') }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end" required autofocus value="{{ old('end') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Max Participants</label>
                                <input type="number" name="max_participants" class="form-control" value="{{ old('max_participants') }}" required />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Description</label>
                                <textarea name="description" class="form-control"></textarea>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="ACTIVE" {{ old('status', 'ACTIVE') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                    <option value="INACTIVE" {{ old('status', 'ACTIVE') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association</label>
                                <select class="form-control" name="association_id">
                                    @foreach ($associations as $association)
                                    <option value="{{ $association->id }}">{{ $association->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @elseif(count($adminObj->associations) > 0)
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association</label>
                                <select class="form-control" name="association_id">
                                    @foreach ($adminObj->associations as $association)
                                    <option value="{{ $association->id }}">{{ $association->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                              <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Competition Fee (RM)</label>
                                <input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price', 0) }}" required />
                            </div>
                        </div>
                        @endif

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