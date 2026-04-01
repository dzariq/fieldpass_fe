@extends('backend.layouts.master')

@section('title')
Player Create - Admin Panel
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
                <h4 class="page-title pull-left">Player Create</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.players.index') }}">All Players</a></li>
                    <li><span>Create Player</span></li>
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
                    <h4 class="header-title">Create New Player</h4>
                    @include('backend.layouts.partials.messages')

                    <form action="{{ route('admin.players.store') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name" class="required-field">Player Name</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Name" required autofocus value="{{ old('name') }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="email">Player Email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter Email (Optional)" value="{{ old('email') }}">
                                <small class="form-text text-muted">Optional</small>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name" >Jersey Number</label>
                                <input type="number" min="1" step="1" class="form-control" id="jersey_number" name="jersey_number" placeholder="Enter Jersey Number" required autofocus value="{{ old('jersey_number') }}">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="username">Admin Username</label>
                                <input type="text" class="form-control bg-light" id="username" name="username" placeholder="Auto-generated from name" readonly value="{{ old('username') }}">
                                <small class="form-text text-muted">Username will be auto-generated with timestamp</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="clubs">Assign Clubs</label>
                                <select name="club_ids[]" id="clubs" class="form-control select2" multiple>
                                    @foreach ($clubs as $club)
                                    <option value="{{ $club->id }}" {{ (old('club_ids') && in_array($club->id, old('club_ids'))) ? 'selected' : '' }}>{{ $club->name }}</option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Optional - Select clubs to assign player to</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="salary">Salary</label>
                                <input type="number" min="0" max="9999999999" class="form-control" id="salary" name="salary" value="{{ old('salary', 0) }}">
                                <small class="form-text text-muted">Optional - Default: 0</small>
                            </div>

                            <div class="form-group col-md-6 col-sm-6">
                                <label for="position" class="required-field">Position</label>
                                <select class="form-control" name="position" id="position" required>
                                    <option value="">{{ __('Select Position') }}</option>
                                    <option value="Goalkeeper" {{ old('position') == 'Goalkeeper' ? 'selected' : '' }}>Goalkeeper</option>
                                    <option value="Defender" {{ old('position') == 'Defender' ? 'selected' : '' }}>Defender</option>
                                    <option value="Midfielder" {{ old('position') == 'Midfielder' ? 'selected' : '' }}>Midfielder</option>
                                    <option value="Forward" {{ old('position') == 'Forward' ? 'selected' : '' }}>Forward</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="identity_number" class="required-field">Identity Number (IC)</label>
                                <input type="text" class="form-control" id="identity_number" name="identity_number" placeholder="XXXXXX-XX-XXXX" required value="{{ old('identity_number') }}" maxlength="14">
                                <!-- <small class="form-text text-muted">Format: XXXXXX-XX-XXXX (e.g., 900101-01-1234)</small> -->
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="country_code" class="required-field">Country Code</label>
                                <select name="country_code" id="country_code" class="form-control" required>
                                    <option value="">Select Country Code</option>
                                    <option value="60" {{ old('country_code') == '60' ? 'selected' : '' }}>+60 (Malaysia)</option>
                                    <option value="65" {{ old('country_code') == '65' ? 'selected' : '' }}>+65 (Singapore)</option>
                                    <option value="62" {{ old('country_code') == '62' ? 'selected' : '' }}>+62 (Indonesia)</option>
                                    <option value="66" {{ old('country_code') == '66' ? 'selected' : '' }}>+66 (Thailand)</option>
                                    <option value="63" {{ old('country_code') == '63' ? 'selected' : '' }}>+63 (Philippines)</option>
                                    <option value="84" {{ old('country_code') == '84' ? 'selected' : '' }}>+84 (Vietnam)</option>
                                    <option value="95" {{ old('country_code') == '95' ? 'selected' : '' }}>+95 (Myanmar)</option>
                                    <option value="856" {{ old('country_code') == '856' ? 'selected' : '' }}>+856 (Laos)</option>
                                    <option value="855" {{ old('country_code') == '855' ? 'selected' : '' }}>+855 (Cambodia)</option>
                                    <option value="673" {{ old('country_code') == '673' ? 'selected' : '' }}>+673 (Brunei)</option>
                                    <option value="670" {{ old('country_code') == '670' ? 'selected' : '' }}>+670 (East Timor)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="phone" class="required-field">Phone Number</label>
                                <input type="text" class="form-control" id="phone" name="phone" placeholder="123456789" required value="{{ old('phone') }}" pattern="[0-9]{7,15}" title="Please enter 7-15 digits">
                                <small class="form-text text-muted">Enter without country code (e.g., 123456789)</small>
                            </div>
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start" value="{{ old('start', date('Y-m-d')) }}">
                                <small class="form-text text-muted">Default: Today</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 col-sm-6">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end" value="{{ old('end') }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">Save</button>
                        <a href="{{ route('admin.players.index') }}" class="btn btn-secondary mt-4 pr-4 pl-4">Cancel</a>
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
        $('.select2').select2({
            placeholder: "Select clubs (optional)",
            allowClear: true
        });

        // Auto-generate unique username from name
        $('#name').on('input', function() {
            let name = $(this).val().trim();
            if (name) {
                // Remove spaces and special characters, convert to lowercase
                let cleanName = name.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();

                // Take first 6-10 characters depending on length
                let baseUsername = cleanName.substring(0, Math.min(cleanName.length, 10));

                // Add timestamp to ensure uniqueness (last 6 digits of timestamp)
                let timestamp = Date.now().toString().slice(-6);

                // Combine: name + timestamp (e.g., "john123456" or "ahmadali456789")
                let username = baseUsername + timestamp;

                // Ensure minimum 6 characters
                if (username.length < 6) {
                    username = username + Math.floor(Math.random() * 100000).toString().padStart(5, '0');
                }

                $('#username').val(username);
            } else {
                $('#username').val('');
            }
        });

        // Auto-format IC number to XXXXXX-XX-XXXX
        // $('#identity_number').on('input', function() {
        //     let value = this.value.replace(/[^0-9]/g, ''); // Remove non-digits

        //     if (value.length > 6) {
        //         value = value.substring(0, 6) + '-' + value.substring(6);
        //     }
        //     if (value.length > 9) {
        //         value = value.substring(0, 9) + '-' + value.substring(9);
        //     }
        //     if (value.length > 14) {
        //         value = value.substring(0, 14);
        //     }

        //     this.value = value;
        // });

        // Validate IC format on blur
        // $('#identity_number').on('blur', function() {
        //     let value = this.value;
        //     let pattern = /^\d{6}-\d{2}-\d{4}$/;

        //     if (value && !pattern.test(value)) {
        //         alert('Invalid IC format. Please use format: XXXXXX-XX-XXXX (e.g., 900101-01-1234)');
        //         $(this).focus();
        //     }
        // });

        // Set start date to today if empty
        if (!$('#start_date').val()) {
            let today = new Date().toISOString().split('T')[0];
            $('#start_date').val(today);
        }

        // Validate phone number (numbers only)
        $('#phone').on('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Trigger username generation on page load if name exists
        if ($('#name').val()) {
            $('#name').trigger('input');
        }
    });
</script>
@endsection