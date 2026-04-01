@extends('playerbackend.layouts.master')

@section('title')
Club Create - Admin Panel
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
                <h4 class="page-title pull-left">Club Create</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.clubs.index') }}">All Clubs</a></li>
                    <li><span>Create Club</span></li>
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
                    <h4 class="header-title">Create New Club</h4>
                    @include('playerbackend.layouts.partials.messages')

                    <form action="{{ route('admin.clubs.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Club Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required autofocus value="{{ old('name') }}">
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