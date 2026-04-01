@extends('backend.layouts.master')

@section('title')
Admin Create - Admin Panel
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
</style>
@endsection


@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">Admin Create</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.admins.index') }}">All Admins</a></li>
                    <li><span>Create Admin</span></li>
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
                    <h4 class="header-title">Create New Admin</h4>
                    @include('backend.layouts.partials.messages')
                    
                    <form action="{{ route('admin.admins.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name" class="required-field">Admin Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required autofocus value="{{ old('name') }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="email">Admin Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email" value="{{ old('email') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="username" class="required-field">Admin Username</label>
                                <input type="text" class="form-control bg-light" id="username" name="username" placeholder="Auto-generated from name" readonly required value="{{ old('username') }}">
                                <small class="form-text text-muted">Username will be auto-generated from name</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="roles" class="required-field">Assign Roles</label>
                                <select name="roles[]" id="roles" class="form-control select2" multiple required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="country_code" class="required-field">Country Code</label>
                                <select name="country_code" id="country_code" class="form-control" required>
                                    <option value="">Select Country Code</option>
                                    <option value="+60" {{ old('country_code') == '+60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                    <option value="+65" {{ old('country_code') == '+65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                    <option value="+62" {{ old('country_code') == '+62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                    <option value="+66" {{ old('country_code') == '+66' ? 'selected' : '' }}>+66 (Thailand)</option>
                                    <option value="+63" {{ old('country_code') == '+63' ? 'selected' : '' }}>+63 (Philippines)</option>
                                    <option value="+84" {{ old('country_code') == '+84' ? 'selected' : '' }}>+84 (Vietnam)</option>
                                    <option value="+95" {{ old('country_code') == '+95' ? 'selected' : '' }}>+95 (Myanmar)</option>
                                    <option value="+856" {{ old('country_code') == '+856' ? 'selected' : '' }}>+856 (Laos)</option>
                                    <option value="+855" {{ old('country_code') == '+855' ? 'selected' : '' }}>+855 (Cambodia)</option>
                                    <option value="+673" {{ old('country_code') == '+673' ? 'selected' : '' }}>+673 (Brunei)</option>
                                    <option value="+670" {{ old('country_code') == '+670' ? 'selected' : '' }}>+670 (East Timor)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter Phone Number" required pattern="[0-9]+" title="Please enter numbers only" value="{{ old('phone') }}">
                            </div>
                        </div>

                        <div class="form-row">
                            @if($association_id == 0 && !$is_club_admin)
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="associations">Assign Associations</label>
                                <select name="association_ids[]" id="associations" class="form-control select2" multiple>
                                    @foreach ($associations as $association)
                                        <option value="{{ $association->id }}">{{ $association->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            @if($club_id == 0 && !$is_club_admin)
                                <div class="form-group col-md-6 col-sm-12">
                                    <label for="clubs">Assign Clubs</label>
                                    <select name="club_ids[]" id="clubs" class="form-control select2" multiple>
                                        @foreach ($clubs as $club)
                                            <option value="{{ $club->id }}">{{ $club->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <input type="hidden" value="{{ $club_id }}" name="club_ids[]" />
                            @endif
                        </div>
                        
                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.admins.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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
        
        // Phone number validation - allow only numbers
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Auto-generate username from name
        $('#name').on('input', function() {
            let name = $(this).val();
            
            // Convert to lowercase, remove special characters, replace spaces with empty string
            let username = name.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '') // Remove special characters
                .replace(/\s+/g, ''); // Remove spaces
            
            // Limit to 20 characters
            username = username.substring(0, 20);
            
            // Add timestamp suffix to make it unique
            if (username.length > 0) {
                let timestamp = Date.now().toString().slice(-6); // Last 6 digits of timestamp
                username = username + timestamp;
            }
            
            $('#username').val(username);
        });

        // Generate username on page load if name has old value
        if ($('#name').val()) {
            $('#name').trigger('input');
        }
    });
</script>
@endsection