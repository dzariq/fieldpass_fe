@extends('backend.layouts.master')

@section('title')
Admin Edit - Admin Panel
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

<style>
    .form-check-label {
        text-transform: capitalize;
    }
    .required-field::after {
        content: " *";
        color: red;
        font-weight: bold;
    }
    .status-badge {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        margin-top: 5px;
    }
    .status-ACTIVE {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .status-INACTIVE {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    .status-INVITED {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }
</style>
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Admin Edit</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.admins.index') }}">All Admins</a></li>
                    <li><span>Edit Admin - {{ $admin->name }}</span></li>
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
                    <h4 class="header-title">Edit Admin - {{ $admin->name }}</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="form-group text-center">
                            <label for="avatar">Avatar</label><br>
                            <img src="{{ $admin->avatar ? asset($admin->avatar) : asset('backend/assets/images/default-avatar.png') }}"
                                alt="Avatar"
                                class="rounded-circle mb-3"
                                width="150" height="150">

                            <input type="file" class="form-control-file mt-2" name="avatar" id="avatar" accept="image/*">
                            <small class="form-text text-muted">Max size: 2MB</small>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name" class="required-field">Admin Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" value="{{ $admin->name }}" required autofocus>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="email">Admin Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" value="{{ $admin->email }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="username" class="required-field">Admin Username</label>
                                <input type="text" class="form-control bg-light" id="username" name="username" placeholder="Enter Username" readonly value="{{ $admin->username }}">
                                <small class="form-text text-muted">Username cannot be changed</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="roles" class="required-field">Assign Roles</label>
                                <select name="roles[]" id="roles" class="form-control select2" multiple required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}" {{ $admin->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="country_code" class="required-field">Country Code</label>
                                <select name="country_code" id="country_code" class="form-control" required>
                                    <option value="">Select Country Code</option>
                                    <option value="+60" {{ (old('country_code', $admin->country_code) == '+60') ? 'selected' : '' }}>+60 (Malaysia)</option>
                                    <option value="+65" {{ (old('country_code', $admin->country_code) == '+65') ? 'selected' : '' }}>+65 (Singapore)</option>
                                    <option value="+62" {{ (old('country_code', $admin->country_code) == '+62') ? 'selected' : '' }}>+62 (Indonesia)</option>
                                    <option value="+66" {{ (old('country_code', $admin->country_code) == '+66') ? 'selected' : '' }}>+66 (Thailand)</option>
                                    <option value="+63" {{ (old('country_code', $admin->country_code) == '+63') ? 'selected' : '' }}>+63 (Philippines)</option>
                                    <option value="+84" {{ (old('country_code', $admin->country_code) == '+84') ? 'selected' : '' }}>+84 (Vietnam)</option>
                                    <option value="+95" {{ (old('country_code', $admin->country_code) == '+95') ? 'selected' : '' }}>+95 (Myanmar)</option>
                                    <option value="+856" {{ (old('country_code', $admin->country_code) == '+856') ? 'selected' : '' }}>+856 (Laos)</option>
                                    <option value="+855" {{ (old('country_code', $admin->country_code) == '+855') ? 'selected' : '' }}>+855 (Cambodia)</option>
                                    <option value="+673" {{ (old('country_code', $admin->country_code) == '+673') ? 'selected' : '' }}>+673 (Brunei)</option>
                                    <option value="+670" {{ (old('country_code', $admin->country_code) == '+670') ? 'selected' : '' }}>+670 (East Timor)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required pattern="[0-9]+" title="Please enter numbers only" value="{{ old('phone', $admin->phone) }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="password">Password (Optional)</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="password_confirmation">Confirm Password (Optional)</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Enter Password">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="associations">Assign Associations</label>
                                <select name="association_ids[]" id="associations" class="form-control select2" multiple>
                                    @foreach ($associations as $association)
                                    <option value="{{ $association->id }}" {{ $admin->associations->contains('id', $association->id) ? 'selected' : '' }}>{{ $association->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="clubs">Assign Clubs</label>
                                <select name="club_ids[]" id="clubs" class="form-control select2" multiple>
                                    @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ $admin->clubs->contains('id', $club->id) ? 'selected' : '' }}>{{ $club->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="status">Admin Status</label>
                                <div>
                                    <span class="status-badge status-{{ $admin->status }}">
                                        {{ $admin->status }}
                                    </span>
                                </div>
                                <small class="form-text text-muted">Status cannot be changed manually</small>
                                <input type="hidden" name="status" value="{{ $admin->status }}">
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary pr-4 pl-4">
                                <i class="fa fa-save"></i> Update Admin
                            </button>
                            <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary pr-4 pl-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            
                            @if($admin->status == 'INVITED')
                            <button type="button" class="btn btn-warning pr-4 pl-4" onclick="document.getElementById('reinvite-form').submit();">
                                <i class="fa fa-envelope"></i> Re-Invite Admin
                            </button>
                            @endif
                        </div>
                    </form>

                    @if($admin->status == 'INVITED')
                    <!-- Re-invite Form (separate form) -->
                    <form id="reinvite-form" action="{{ route('admin.admins.reinvite', $admin->id) }}" method="POST" style="display: none;">
                        @csrf
                    </form>
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
        
        // Phone number validation - allow only numbers
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    })
</script>
@endsection