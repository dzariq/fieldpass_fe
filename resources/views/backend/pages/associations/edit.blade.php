
@extends('backend.layouts.master')

@section('title')
Association Edit - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Association Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.associations.index') }}">All Associations</a></li>
                    <li><span>Edit Association - {{ $association->name }}</span></li>
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
                    <h4 class="header-title">Edit Association - {{ $association->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.associations.update', $association->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf

                        <div class="form-group text-center">
                            <label for="avatar">Avatar</label><br>
                            <img src="{{ $association->avatar ? asset($association->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                alt="Avatar"
                                class="rounded-circle mb-3"
                                width="150" height="150">

                            <input type="file" class="form-control-file mt-2" name="avatar" id="avatar" accept="image/*">
                            <small class="form-text text-muted">Max size: 2MB</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Association Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ $association->name }}" required autofocus>
                            </div>
                           
                        </div>

                     
                       

                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.associations.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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