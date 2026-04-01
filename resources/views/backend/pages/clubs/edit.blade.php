@extends('backend.layouts.master')

@section('title')
Club Edit - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .image-preview {
        max-width: 100%;
        max-height: 200px;
        height: auto;
        margin-top: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 5px;
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
                <h4 class="page-title pull-left">Club Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.clubs.index') }}">All Clubs</a></li>
                    <li><span>Edit Club - {{ $club->name }}</span></li>
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
                    <h4 class="header-title">Edit Club - {{ $club->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.clubs.update', $club->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="form-group text-center">
                            <label for="avatar">Avatar</label><br>
                            <img src="{{ $club->avatar ? asset($club->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                alt="Avatar"
                                class="rounded-circle mb-3"
                                width="150" height="150">

                            <input type="file" class="form-control-file mt-2" name="avatar" id="avatar" accept="image/*">
                            <small class="form-text text-muted">Max size: 2MB</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="jersey">Club Jersey</label>
                                @if($club->jersey)
                                    <div>
                                        <img src="{{ asset($club->jersey) }}" alt="Current Jersey" class="image-preview">
                                    </div>
                                @endif
                                <input type="file" class="form-control-file mt-2" name="jersey" id="jersey" accept="image/jpeg,image/png,image/jpg">
                                <small class="form-text text-muted">Upload JPG or PNG file. Max size: 2MB</small>
                            </div>
                        </div>

                        <input type="hidden" name="association_id" value="{{ $club->association_id }}" >

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Club Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ $club->name }}" required autofocus>
                            </div>

                        </div>
                         <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="long_name">Club Long Name</label>
                                <input type="text" class="form-control" id="long_name" name="long_name" placeholder="Enter Long Name" value="{{ $club->long_name }}" required autofocus>
                            </div>

                        </div>


                        @if ($usr->can('association.create'))
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association</label>
                                <select class="form-control" name="association_id">
                                    @foreach ($associations as $association)
                                    <option value="{{ $association->id }}"
                                        {{ old('association_id', $club->association_id) == $association->id ? 'selected' : '' }}>
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
                                <select class="form-control" name="association_id">
                                    @foreach ($adminObj->associations as $association)
                                        <option value="{{ $association->id }}"
                                            {{ old('association_id', $club->association_id) == $association->id ? 'selected' : '' }}>
                                            {{ $association->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif

                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.clubs.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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